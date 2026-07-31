<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_fa599532ba07189f\muqsit\simplepackethandler;

use InvalidArgumentException;
use IvanCraft623\RankSystem\libs\_fa599532ba07189f\muqsit\simplepackethandler\interceptor\IPacketInterceptor;
use IvanCraft623\RankSystem\libs\_fa599532ba07189f\muqsit\simplepackethandler\interceptor\PacketInterceptor;
use IvanCraft623\RankSystem\libs\_fa599532ba07189f\muqsit\simplepackethandler\monitor\IPacketMonitor;
use IvanCraft623\RankSystem\libs\_fa599532ba07189f\muqsit\simplepackethandler\monitor\PacketMonitor;
use pocketmine\event\EventPriority;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\plugin\Plugin;

final class SimplePacketHandler{

	public static function createInterceptor(Plugin $registerer, int $priority = EventPriority::NORMAL, bool $handle_cancelled = false) : IPacketInterceptor{
		if($priority === EventPriority::MONITOR){
			throw new InvalidArgumentException("Cannot intercept packets at MONITOR priority");
		}
		return new PacketInterceptor($registerer, PacketPool::getInstance(), $priority, $handle_cancelled);
	}

	public static function createMonitor(Plugin $registerer, bool $handle_cancelled = false) : IPacketMonitor{
		return new PacketMonitor($registerer, PacketPool::getInstance(), $handle_cancelled);
	}
}