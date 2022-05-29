<?php
declare(strict_types=1);

namespace Riding\entity;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class LlamaRiding extends RidingEntity{

	public static function getNetworkTypeId() : string{
		return EntityIds::LLAMA;
	}

	public $height = 1.87;

	public $width = 0.9;

	public function getName() : string{
		return "LlamaRiding";
	}

	public function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1.87, 0.9);
	}

	public function getSpeed() : float{
		return 1.2;
	}
}