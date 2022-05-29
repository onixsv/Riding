<?php
declare(strict_types=1);

namespace Riding\entity;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class ZombieRiding extends RidingEntity{

	public static function getNetworkTypeId() : string{
		return EntityIds::ZOMBIE;
	}

	public function getName() : string{
		return "ZombieRiding";
	}

	public function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1.95, 0.6);
	}

	public function getSpeed() : float{
		return 1.2;
	}
}