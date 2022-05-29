<?php
declare(strict_types=1);

namespace Riding\entity;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class PigRiding extends RidingEntity implements SmallEntity{

	public static function getNetworkTypeId() : string{
		return EntityIds::PIG;
	}

	public $width = 0.9;

	public $height = 0.9;

	public function getName() : string{
		return "PigRiding";
	}

	public function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(0.9, 0.9);
	}

	public function getSpeed() : float{
		return 1.2;
	}
}