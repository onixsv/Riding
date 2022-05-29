<?php
declare(strict_types=1);

namespace Riding\entity;

use pocketmine\entity\Living;

abstract class RidingEntity extends Living{
	use AITrait;
}