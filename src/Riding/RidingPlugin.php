<?php
declare(strict_types=1);

namespace Riding;

use OnixUtils\OnixUtils;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;
use ReflectionClass;
use Riding\commands\BuyRidingCommand;
use Riding\commands\RemoveRidingCommand;
use Riding\commands\RidingListCommand;
use Riding\commands\SpawnRidingCommand;
use Riding\entity\HumanEntity;
use Riding\entity\RidingEntity;
use function array_values;
use function in_array;
use function strtolower;

class RidingPlugin extends PluginBase implements Listener{
	use SingletonTrait;

	/** @var Config */
	protected Config $config;

	protected array $db;

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->config = new Config($this->getDataFolder() . "Config.yml", Config::YAML, []);
		$this->db = $this->config->getAll();

		$this->getServer()->getCommandMap()->registerAll("riding", [
			new BuyRidingCommand(),
			new RemoveRidingCommand(),
			new RidingListCommand(),
			new SpawnRidingCommand()
		]);

		foreach(array_values(RidingQueue::$ridingList) as $class){
			EntityFactory::getInstance()->register($class, function(World $world, CompoundTag $nbt) use ($class) : Entity{
				return new $class(EntityDataHelper::parseLocation($nbt, $world), $nbt);
			}, [
				(new ReflectionClass($class))->getShortName(),
				"alvin0319:" . (new ReflectionClass($class))->getShortName()
			]);
		}
	}

	public function close($entity){
		if($entity instanceof RidingEntity || $entity instanceof HumanEntity){
			foreach(RidingQueue::$spawnQueue as $playerName => $entity_1){
				if($entity->getOwnerName() === $entity_1->getOwnerName()){
					unset(RidingQueue::$spawnQueue[$playerName]);
					if(($player = $this->getServer()->getPlayerExact($entity->getOwnerName())) instanceof Player){
						OnixUtils::message($player, "라이딩이 죽었습니다!");
					}
					if(isset(RidingQueue::$riderList[$playerName])){
						unset(RidingQueue::$riderList[$playerName]);
					}
				}
			}
		}
	}

	protected function onDisable() : void{
		$this->config->setAll($this->db);
		$this->config->save();

		foreach($this->getServer()->getWorldManager()->getWorlds() as $level){
			foreach($level->getEntities() as $entity){
				if($entity instanceof RidingEntity){
					$entity->close();
				}
			}
		}
	}

	public function getPlayerRidingPets(Player $player) : array{
		return $this->db[$player->getName()] ?? [];
	}

	public function addRidingPet(Player $player, string $pet) : void{
		$this->db[$player->getName()][] = $pet;
	}

	public function hasRiding(Player $player, string $pet) : bool{
		return in_array($pet, $this->getPlayerRidingPets($player));
	}

	public function handlePlayerJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		if(!isset($this->db[$player->getName()])){
			$this->db[$player->getName()] = [];
		}
	}

	public function handleDataPacketReceive(DataPacketReceiveEvent $event){
		$player = $event->getOrigin()->getPlayer();
		$packet = $event->getPacket();

		if(!$player instanceof Player){
			return;
		}

		switch(true){
			case ($packet instanceof InventoryTransactionPacket):
				if($packet->trData instanceof UseItemOnEntityTransactionData){
					$entity = $player->getWorld()->getEntity($packet->trData->getActorRuntimeId());
					if($entity instanceof RidingEntity && !$entity instanceof HumanEntity){
						if($entity->getOwnerName() === strtolower($player->getName())){
							$entity->setLink(true);
							RidingQueue::$riderList[$player->getName()] = $entity;
						}
					}
				}
				break;
			case ($packet instanceof PlayerInputPacket):
				if(($riding = $this->getRiding($player)) instanceof RidingEntity){
					if(!$riding->isClosed())
						$riding->doRidingMovement($packet->motionX, $packet->motionY);
				}
				break;
			case ($packet instanceof InteractPacket):
				if($packet->action === InteractPacket::ACTION_LEAVE_VEHICLE){
					if(($riding = $this->getRiding($player)) instanceof RidingEntity){
						$riding->setLink(false);
					}
				}
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 *
	 * @handleCancelled true
	 */
	public function handleEntityDamage(EntityDamageEvent $event){
		$entity = $event->getEntity();
		if($entity instanceof RidingEntity){
			$event->cancel();
			if(!$entity instanceof HumanEntity){
				if($event instanceof EntityDamageByEntityEvent){
					$player = $event->getDamager();
					if($player instanceof Player){
						if($entity->getOwnerName() === strtolower($player->getName())){
							$entity->addLink($player);
							$entity->setLink(true);
							RidingQueue::$riderList[$player->getName()] = $entity;
						}
					}
				}
			}
		}
	}

	public function handleTeleport(EntityTeleportEvent $event){
		$player = $event->getEntity();

		if($player instanceof Player){
			if(($riding = $this->getRiding($player)) instanceof RidingEntity){
				if(!$riding->isClosed()){
					$riding->close();
					unset(RidingQueue::$riderList[$player->getName()]);
				}
			}
		}
	}

	public function handleQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		if(($riding = $this->getRiding($player)) instanceof RidingEntity){
			if(!$riding->isClosed()){
				$riding->close();
				unset(RidingQueue::$riderList[$player->getName()]);
			}
		}
	}

	public function getRiding(Player $player) : ?RidingEntity{
		return RidingQueue::$riderList[$player->getName()] ?? null;
	}
}