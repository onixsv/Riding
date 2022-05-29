<?php
declare(strict_types=1);

namespace Riding\commands;

use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\EntityDataHelper;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use Riding\entity\HumanEntity;
use Riding\RidingPlugin;
use Riding\RidingQueue;
use function array_keys;
use function in_array;

class SpawnRidingCommand extends Command{

	public function __construct(){
		parent::__construct("라이딩 스폰", "라이딩을 스폰합니다.");
	}

	public function execute(CommandSender $sender, string $label, array $args) : bool{
		if($sender instanceof Player){
			if(in_array($args[0] ?? "", array_keys(RidingQueue::$ridingList))){
				if(RidingPlugin::getInstance()->hasRiding($sender, $args[0])){
					if(!isset(RidingQueue::$spawnQueue[$sender->getName()])){
						if($sender->getWorld()->getFolderName() !== "content"){
							$nbt = EntityDataHelper::createBaseNBT($sender->getPosition()->add(0, 1, 2));
							$nbt->setString("owner", $sender->getName());
							if($args[0] === "미니미"){
								$nbt->setTag("Skin", CompoundTag::create()->setString("Name", $sender->getSkin()->getSkinId())->setByteArray("Data", $sender->getSkin()->getSkinData())->setByteArray("CapeData", $sender->getSkin()->getCapeData())->setString("GeometryName", $sender->getSkin()->getSkinId())->setByteArray("GeometryData", $sender->getSkin()->getGeometryData()));
							}
							$class = RidingQueue::$ridingList[$args[0]];
							$entity = ($args[0] === "미니미" ? new HumanEntity($sender->getLocation(), $sender->getSkin(), $nbt) : new $class($sender->getLocation(), $nbt));
							$entity->setNameTagAlwaysVisible(true);
							if($args[0] !== "미니미"){
								$entity->setNameTag("§d§l[ §f{$sender->getName()}님의 라이딩 §d]");
							}else{
								$entity->setNameTag("§d§l{$sender->getName()}§f님의 미니미");
								$entity->setScale(0.5);
							}
							$entity->spawnToAll();
							RidingQueue::$spawnQueue[$sender->getName()] = $entity;
						}else{
							OnixUtils::message($sender, "컨텐츠 월드에서는 라이딩 스폰이 불가능 합니다.");
						}
					}else{
						OnixUtils::message($sender, "이미 라이딩이 소환되어 있습니다.");
					}
				}else{
					OnixUtils::message($sender, "당신은 이 라이딩을 보유하고 있지 않습니다.");
				}
			}else{
				OnixUtils::message($sender, "해당 라이딩은 존재하지 않습니다.");
			}
		}else{
			OnixUtils::message($sender, "플레이어만 사용 가능합니다.");
		}
		return true;
	}
}