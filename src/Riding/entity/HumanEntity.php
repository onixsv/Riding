<?php

declare(strict_types=1);

namespace Riding\entity;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class HumanEntity extends Human{
	use AITrait;

	public static function getNetworkTypeId() : string{
		return EntityIds::PLAYER;
	}

	public function getName() : string{
		return "MiniPet";
	}

	public function getSpeed() : float{
		return 1;
	}

	public function tryLookAt(Entity $target) : void{
		$this->lookAt($target->getLocation());
	}
}