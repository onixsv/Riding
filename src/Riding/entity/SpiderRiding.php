<?php
declare(strict_types=1);

namespace Riding\entity;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class SpiderRiding extends RidingEntity implements SmallEntity{

	public static function getNetworkTypeId() : string{
		return EntityIds::SPIDER;
	}

	public $height = 0.9;

	public $width = 0.4;

	public function getName() : string{
		return "SpiderRiding";
	}

	public function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(0.9, 0.4);
	}

	public function getSpeed() : float{
		return 1.2;
	}
}