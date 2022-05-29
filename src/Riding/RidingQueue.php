<?php
declare(strict_types=1);

namespace Riding;

use Riding\entity\ChickenRiding;
use Riding\entity\DolphinRiding;
use Riding\entity\HumanEntity;
use Riding\entity\LlamaRiding;
use Riding\entity\PigRiding;
use Riding\entity\RidingEntity;
use Riding\entity\SpiderRiding;
use Riding\entity\TurtleRiding;
use Riding\entity\WolfRiding;
use Riding\entity\ZombieRiding;

final class RidingQueue{

	public static array $riderList = [];

	/** @var string|RidingEntity[] */
	public static $ridingList = [
		"좀비" => ZombieRiding::class,
		"돌고래" => DolphinRiding::class,
		"늑대" => WolfRiding::class,
		"라마" => LlamaRiding::class,
		"거북이" => TurtleRiding::class,
		"돼지" => PigRiding::class,
		"닭" => ChickenRiding::class,
		"거미" => SpiderRiding::class,
		"미니미" => HumanEntity::class
	];

	public static array $cashList = [
		"좀비" => 5000,
		"돌고래" => 3000,
		"늑대" => 4000,
		"라마" => 5000,
		"거북이" => 5000,
		"돼지" => 5000,
		"닭" => 3000,
		"거미" => 7000,
		"미니미" => 15000
	];

	/** @var RidingEntity[] */
	public static array $spawnQueue = [];
}