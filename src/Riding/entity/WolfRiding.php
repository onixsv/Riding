<?php
declare(strict_types=1);

namespace Riding\entity;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class WolfRiding extends RidingEntity implements SmallEntity{

	public static function getNetworkTypeId() : string{
		return EntityIds::WOLF;
	}

	public $height = 0.85;

	public $width = 0.6;

	public function getName() : string{
		return "WolfRiding";
	}

	public function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(0.85, 0.6);
	}

	public function getSpeed() : float{
		return 1.2;
	}
}