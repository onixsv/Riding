<?php
declare(strict_types=1);

namespace Riding\entity;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class ChickenRiding extends RidingEntity implements SmallEntity{

	public $height = 0.7;

	public $width = 0.4;

	public static function getNetworkTypeId() : string{
		return EntityIds::CHICKEN;
	}

	public function getName() : string{
		return "ChickenRiding";
	}

	public function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(0.7, 0.4);
	}

	public function getSpeed() : float{
		return 1.2;
	}
}