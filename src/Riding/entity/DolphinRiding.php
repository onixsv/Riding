<?php
declare(strict_types=1);

namespace Riding\entity;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class DolphinRiding extends RidingEntity implements SmallEntity{

	public static function getNetworkTypeId() : string{
		return EntityIds::DOLPHIN;
	}

	public $width = 0.5;

	public $height = 0.5;

	public function getName() : string{
		return "DolphinRiding";
	}

	public function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(0.5, 0.5);
	}

	public function getSpeed() : float{
		return 1.2;
	}
}