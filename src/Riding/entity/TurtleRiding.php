<?php
declare(strict_types=1);

namespace Riding\entity;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class TurtleRiding extends RidingEntity implements SmallEntity{

	public static function getNetworkTypeId() : string{
		return EntityIds::TURTLE;
	}

	public $height = 0.12;

	public $width = 0.36;

	public function getName() : string{
		return "TurtleRiding";
	}

	public function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(0.12, 0.36);
	}

	public function getSpeed() : float{
		return 1.2;
	}
}