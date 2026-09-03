<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_7c604e93589d947c\muqsit\simplepackethandler\monitor;

use Closure;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ServerboundPacket;

interface IPacketMonitor{

	/**
	 * @template TServerboundPacket of ServerboundPacket
	 * @param Closure(TServerboundPacket, NetworkSession) : void $handler
	 * @return IPacketMonitor
	 */
	public function monitorIncoming(Closure $handler) : IPacketMonitor;

	/**
	 * @template TClientboundPacket of ClientboundPacket
	 * @param Closure(TClientboundPacket, NetworkSession) : void $handler
	 * @return IPacketMonitor
	 */
	public function monitorOutgoing(Closure $handler) : IPacketMonitor;

	/**
	 * @template TServerboundPacket of ServerboundPacket
	 * @param Closure(TServerboundPacket, NetworkSession) : void $handler
	 * @return IPacketMonitor
	 */
	public function unregisterIncomingMonitor(Closure $handler) : IPacketMonitor;

	/**
	 * @template TClientboundPacket of ClientboundPacket
	 * @param Closure(TClientboundPacket, NetworkSession) : void $handler
	 * @return IPacketMonitor
	 */
	public function unregisterOutgoingMonitor(Closure $handler) : IPacketMonitor;
}