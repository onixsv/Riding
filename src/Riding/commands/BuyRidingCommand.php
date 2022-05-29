<?php
declare(strict_types=1);

namespace Riding\commands;

use Cash\Cash;
use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Riding\RidingPlugin;
use Riding\RidingQueue;
use function array_keys;
use function implode;
use function in_array;

class BuyRidingCommand extends Command{

	public function __construct(){
		parent::__construct("라이딩 구매", "라이딩을 구매합니다.");
	}

	public function execute(CommandSender $sender, string $label, array $args) : bool{
		if($sender instanceof Player){
			if(in_array($args[0] ?? "", array_keys(RidingQueue::$ridingList))){
				$data = RidingPlugin::getInstance()->getPlayerRidingPets($sender);
				if(!in_array($args[0], $data)){
					if(Cash::getInstance()->getCash($sender) >= RidingQueue::$cashList[$args[0]]){
						RidingPlugin::getInstance()->addRidingPet($sender, $args[0]);
						OnixUtils::message($sender, "{$args[0]} 라이딩을 구매하였습니다.");
						Cash::getInstance()->reduceCash($sender, RidingQueue::$cashList[$args[0]]);
					}else{
						OnixUtils::message($sender, "{$args[0]} 라이딩을 구매하려면 " . RidingQueue::$cashList[$args[0]] . " 캐시가 필요합니다.");
					}
				}else{
					OnixUtils::message($sender, "이미 해당 라이딩을 구매하셨습니다.");
				}
			}else{
				OnixUtils::message($sender, "해당 이름의 라이딩은 존재하지 않습니다.");
				OnixUtils::message($sender, "라이딩 목록: " . implode(", ", array_keys(RidingQueue::$ridingList)));
			}
		}else{
			OnixUtils::message($sender, "플레이어만 사용할 수 있습니다.");
		}
		return true;
	}
}