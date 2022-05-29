<?php

declare(strict_types=1);

namespace Riding\entity;

use muqsit\invmenu\InvMenu;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\math\Math;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\world\Position;
use Riding\RidingPlugin;
use function sqrt;
use function strtolower;

trait AITrait{
	/** @var Player */
	protected ?Player $owner;

	/** @var Player */
	protected ?Player $rider;

	protected $jumpTicks = 20;

	/** @var float */
	protected $follow_range_sq = 1.2;

	protected $ownerName;

	protected $rider_seatpos;

	protected $linked = false;
	/** @var InvMenu */
	protected $inv;

	public function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);
		$owner = $nbt->getString("owner", "");
		$this->ownerName = $owner;
		$this->rider = $this->server->getPlayerExact($owner);
		$this->owner = $this->rider;

		$scale = $this->getScale();
		if($this instanceof SmallEntity){
			$this->rider_seatpos = new Vector3(0, 0.78 + $scale * 0.9, -0.25);
		}else{
			$this->rider_seatpos = new Vector3(0, 1.8 + $scale * 0.9, -0.25);
		}

		$this->inv = InvMenu::create(InvMenu::TYPE_HOPPER);

		if($nbt->getTag("RidingInventory") instanceof ListTag){
			foreach($nbt->getListTag("RidingInventory")->getValue() as $itemNBT){
				if($itemNBT instanceof CompoundTag){
					$slot = $itemNBT->getByte("Slot");
					$this->inv->getInventory()->setItem($slot, Item::nbtDeserialize($itemNBT));
				}
			}
		}
	}

	public function getOwnerName() : string{
		return strtolower($this->ownerName ?? "");
	}

	abstract public function getSpeed() : float;

	public function addLink(Player $player, bool $saddle = true) : void{
		if($saddle){
			$link = new EntityLink(0, 0, 0, true, true);
			$link->fromActorUniqueId = $this->getId();
			$link->type = 1;
			$link->toActorUniqueId = $player->getId();
			$link->immediate = true;
			$pk = new SetActorLinkPacket();
			$pk->link = $link;
			//$player->sendDataPacket($pk);
			$this->server->broadcastPackets($this->getViewers(), [$pk]);
			$link_2 = new EntityLink(0, 0, 0, true, true);
			$link_2->fromActorUniqueId = $this->getId();
			$link_2->type = 1;
			$link_2->toActorUniqueId = 0;
			$link_2->immediate = true;
			$pk = new SetActorLinkPacket();
			$pk->link = $link_2;
			//$player->sendDataPacket($pk);
			$this->server->broadcastPackets($this->getViewers(), [$pk]);

			$player->getNetworkProperties()->setVector3(EntityMetadataProperties::RIDER_SEAT_POSITION, $this->rider_seatpos);
			$this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::RIDING, true);
			$this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SADDLED, true);
		}
	}

	public function setLink(bool $v){
		$this->linked = $v;
		if($v){
			$this->addLink($this->owner);
		}else{
			$this->owner->getNetworkProperties()->setGenericFlag(EntityMetadataProperties::RIDER_MAX_ROTATION, false);
		}
	}

	public function doRidingMovement(float $motionX, float $motionZ) : void{
		$speed_factor = 2.5 * $this->getSpeed();
		$direction_plane = $this->getDirectionPlane();
		$x = $direction_plane->x / $speed_factor;
		$z = $direction_plane->y / $speed_factor;

		$this->location->pitch = $this->rider->getLocation()->getPitch();
		$this->location->yaw = $this->rider->getLocation()->getYaw();

		$this->setPositionAndRotation($this->location, $this->location->yaw, $this->location->pitch);

		$this->sendData($this->getViewers());

		switch($motionZ){
			case 1:
				$finalMotionX = $x;
				$finalMotionZ = $z;
				break;
			case -1:
				$finalMotionX = -$x;
				$finalMotionZ = -$z;
				break;
			default:
				$average = $x + $z / 2;
				$finalMotionX = $average / 1.414 * $motionZ;
				$finalMotionZ = $average / 1.414 * $motionX;
				break;
		}

		switch($motionX){
			case 1:
				$finalMotionX = $z;
				$finalMotionZ = -$x;
				break;
			case -1:
				$finalMotionX = -$z;
				$finalMotionZ = $x;
				break;
		}

		$this->move($finalMotionX, $this->motion->y, $finalMotionZ);
		$this->updateMovement();
	}

	public function onUpdate(int $currentTick) : bool{
		$hasUpdate = parent::onUpdate($currentTick);

		if(!$this->rider instanceof Player){
			$this->close();
			return false;
		}

		if(!$this->rider->isConnected()){
			$this->close();
			return false;
		}

		if($this->jumpTicks > 0){
			--$this->jumpTicks;
		}

		if(!$this->isOnGround()){
			if($this->motion->y > -$this->gravity * 4){
				$this->motion->y = -$this->gravity * 4;
			}else{
				$this->motion->y += $this->isUnderwater() ? $this->gravity : -$this->gravity;
			}
		}else{
			if($this->jumpTicks === 0){
				if($this->isCollidedHorizontally){
					$this->jump();
				}
			}else{
				$this->motion->y -= $this->gravity;
			}
		}

		if($this->checkJump()){
			$this->jump();
		}

		if(!$this->linked){
			if($this->getPosition()->distance($this->rider->getPosition()) < 10){
				$this->follow($this->rider);
			}else{
				$this->teleport($this->rider->getPosition());
			}
		}else{
			/*
			$this->server->broadcastPackets($this->hasSpawned, [MoveActorAbsolutePacket::create(
				$this->id,
				$this->getOffsetPosition($this->location),

				//this looks very odd but is correct as of 1.5.0.7
				//for arrows this is actually x/y/z rotation
				//for mobs x and z are used for pitch and yaw, and y is used for headyaw
				$this->location->pitch,
				$this->location->yaw,
				$this->location->yaw,
				MoveActorAbsolutePacket::FLAG_GROUND
			)]);
			*/
			$this->getWorld()->broadcastPacketToViewers($this->location, MoveActorAbsolutePacket::create($this->id, $this->getOffsetPosition($this->location), $this->location->pitch, $this->location->yaw, $this->location->yaw, MoveActorAbsolutePacket::FLAG_GROUND));
			$this->rider->getNetworkSession()->sendDataPacket(MoveActorAbsolutePacket::create($this->id, $this->getOffsetPosition($this->location), $this->location->pitch, $this->location->yaw, $this->location->yaw, MoveActorAbsolutePacket::FLAG_GROUND));
		}

		return $hasUpdate;
	}

	public function checkJump() : bool{
		$direction = $this->getHorizontalFacing();

		$face = $this->getPosition()->getSide($direction);

		$block = $this->getWorld()->getBlock($face);

		if($block->canBeFlowedInto()){
			return false;
		}

		$block = $block->getPosition()->getWorld()->getBlock($block->getPosition()->add(0, 1, 0));

		if($block->canBeFlowedInto()){
			return false;
		}
		return true;
	}

	public function follow(Entity $target, float $xOffset = 0.0, float $yOffset = 0.0, float $zOffset = 0.0) : void{
		$x = $target->getPosition()->getX() + $xOffset - $this->getPosition()->getX();
		$y = $target->getPosition()->getY() + $yOffset - $this->getPosition()->getY();
		$z = $target->getPosition()->getZ() + $zOffset - $this->getPosition()->getZ();

		$xz_sq = $x * $x + $z * $z;
		$xz_modulus = sqrt($xz_sq);

		if($xz_sq < $this->follow_range_sq){
			$this->motion->x = 0;
			$this->motion->z = 0;
		}else{
			$speed_factor = $this->getSpeed() * 0.15;
			$this->motion->x = $speed_factor * ($x / $xz_modulus);
			$this->motion->z = $speed_factor * ($z / $xz_modulus);
		}

		$this->tryLookAt($target);

		$this->move($this->motion->x, $this->motion->y, $this->motion->z);
	}

	public static function getFloorPos(Vector3 $pos) : Position{
		$newPos = new Position(Math::floorFloat($pos->x), $pos->getFloorY(), Math::floorFloat($pos->z), null);
		if($pos instanceof Position){
			$newPos->world = $pos->world;
		}
		return $newPos;
	}

	public function jump() : void{
		//parent::jump();
		$this->motion->y += 1;
		$this->jumpTicks = 20;
	}

	public function onDispose() : void{
		parent::onDispose();
		RidingPlugin::getInstance()->close($this);
	}

	public function canSaveWithChunk() : bool{
		return false;
	}

	public function attack(EntityDamageEvent $event) : void{
		$event->cancel();
	}

	public function tryLookAt(Entity $target) : void{
		$this->lookAt($target->getPosition()->add(0, $target->getEyeHeight(), 0));
	}
}