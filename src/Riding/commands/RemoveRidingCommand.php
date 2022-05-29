<?php
declare(strict_types=1);

namespace Riding\commands;

use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Riding\RidingQueue;

class RemoveRidingCommand extends Command{

	public function __construct(){
		parent::__construct("라이딩 제거", "라이딩을 제거합니다.");
	}

	public function execute(CommandSender $sender, string $label, array $args) : bool{
		if($sender instanceof Player){
			if(isset(RidingQueue::$spawnQueue[$sender->getName()])){
				$riding = RidingQueue::$spawnQueue[$sender->getName()];
				if($riding->isAlive() && !$riding->isClosed()){
					$riding->close();
				}
				OnixUtils::message($sender, "라이딩을 제거했습니다.");
				unset(RidingQueue::$spawnQueue[$sender->getName()]);

				if(isset(RidingQueue::$riderList[$sender->getName()])){
					unset(RidingQueue::$riderList[$sender->getName()]);
				}
			}else{
				OnixUtils::message($sender, "당신은 아직 라이딩을 소환하지 않았습니다.");
			}
		}else{
			OnixUtils::message($sender, "플레이어만 사용 가능합니다.");
		}
		return true;
	}
}