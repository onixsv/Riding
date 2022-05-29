<?php
declare(strict_types=1);

namespace Riding\commands;

use OnixUtils\OnixUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Riding\RidingQueue;

class RidingListCommand extends Command{

	public function __construct(){
		parent::__construct("라이딩 목록", "라이딩 목록을 확인합니다.");
	}

	public function execute(CommandSender $sender, string $label, array $args) : bool{
		OnixUtils::message($sender, "라이딩 목록입니다.");
		foreach(RidingQueue::$cashList as $name => $cash){
			OnixUtils::message($sender, $name . ": " . $cash . "캐시");
		}
		return true;
	}
}