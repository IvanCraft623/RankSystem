<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_dd2697c4b4b2f1c0\muqsit\simplepackethandler\monitor;

use Closure;
use IvanCraft623\RankSystem\libs\_dd2697c4b4b2f1c0\muqsit\simplepackethandler\utils\Utils;
use pocketmine\event\EventPriority;
use pocketmine\event\HandlerListManager;
use pocketmine\event\RegisteredListener;
use pocketmine\event\server\DataPacketDecodeEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use function count;
use function spl_object_id;

final class PacketMonitorListener implements IPacketMonitor{

	/** @var RegisteredListener<DataPacketReceiveEvent>|null */
	private ?RegisteredListener $incoming_event_handler = null;

	/** @var RegisteredListener<DataPacketSendEvent>|null */
	private ?RegisteredListener $outgoing_event_handler = null;

	/** @var RegisteredListener<DataPacketDecodeEvent>|null */
	private ?RegisteredListener $decode_event_handler = null;

	/** @var array<int, array<int, Closure(ServerboundPacket, NetworkSession) : void>> */
	private array $incoming_handlers = [];

	/** @var array<int, array<int, Closure(ClientboundPacket, NetworkSession) : void>> */
	private array $outgoing_handlers = [];

	public function __construct(
		readonly private Plugin $register,
		readonly private PacketPool $pool,
		readonly private bool $handle_cancelled
	){}

	/**
	 * @template TPacket of Packet
	 * @template UPacket of TPacket
	 * @param Closure(UPacket, NetworkSession) : void $handler
	 * @param class-string<TPacket> $class
	 * @return non-empty-list<int>
	 */
	private function parsePidsFromHandler(Closure $handler, string $class) : array{
		$classes = Utils::parseClosureSignature($handler, [$class, NetworkSession::class], "void");
		return Utils::flattenPacketPidsFromGroups($this->pool, $classes[0]);
	}

	private function onStateChange() : void{
		if(count($this->incoming_handlers) > 0 || count($this->outgoing_handlers) > 0){
			$this->decode_event_handler ??= Server::getInstance()->getPluginManager()->registerEvent(DataPacketDecodeEvent::class, function(DataPacketDecodeEvent $event) : void{
				$pid = $event->getPacketId();
				if(isset($this->incoming_handlers[$pid]) || isset($this->outgoing_handlers[$pid])){
					$event->uncancel();
				}
			}, EventPriority::NORMAL, $this->register, true);
		}elseif($this->decode_event_handler !== null){
			HandlerListManager::global()->getListFor(DataPacketDecodeEvent::class)->unregister($this->decode_event_handler);
			$this->decode_event_handler = null;
		}
	}

	public function monitorIncoming(Closure $handler) : IPacketMonitor{
		foreach($this->parsePidsFromHandler($handler, ServerboundPacket::class) as $pid){
			$this->incoming_handlers[$pid][spl_object_id($handler)] = $handler;
		}
		$this->incoming_event_handler ??= Server::getInstance()->getPluginManager()->registerEvent(DataPacketReceiveEvent::class, function(DataPacketReceiveEvent $event) : void{
			/** @var DataPacket&ServerboundPacket $packet */
			$packet = $event->getPacket();
			if(isset($this->incoming_handlers[$pid = $packet::NETWORK_ID])){
				$origin = $event->getOrigin();
				foreach($this->incoming_handlers[$pid] as $handler){
					$handler($packet, $origin);
				}
			}
		}, EventPriority::MONITOR, $this->register, $this->handle_cancelled);
		$this->onStateChange();
		return $this;
	}

	public function monitorOutgoing(Closure $handler) : IPacketMonitor{
		foreach($this->parsePidsFromHandler($handler, ClientboundPacket::class) as $pid){
			$this->outgoing_handlers[$pid][spl_object_id($handler)] = $handler;
		}
		$this->outgoing_event_handler ??= Server::getInstance()->getPluginManager()->registerEvent(DataPacketSendEvent::class, function(DataPacketSendEvent $event) : void{
			/** @var DataPacket&ClientboundPacket $packet */
			foreach($event->getPackets() as $packet){
				if(isset($this->outgoing_handlers[$pid = $packet::NETWORK_ID])){
					foreach($event->getTargets() as $target){
						foreach($this->outgoing_handlers[$pid] as $handler){
							$handler($packet, $target);
						}
					}
				}
			}
		}, EventPriority::MONITOR, $this->register, $this->handle_cancelled);
		$this->onStateChange();
		return $this;
	}

	public function unregisterIncomingMonitor(Closure $handler) : IPacketMonitor{
		$hid = spl_object_id($handler);
		foreach($this->parsePidsFromHandler($handler, ServerboundPacket::class) as $pid){
			if(isset($this->incoming_handlers[$pid][$hid])){
				unset($this->incoming_handlers[$pid][$hid]);
				if(count($this->incoming_handlers[$pid]) === 0){
					unset($this->incoming_handlers[$pid]);
					if(count($this->incoming_handlers) === 0){
						HandlerListManager::global()->getListFor(DataPacketReceiveEvent::class)->unregister($this->incoming_event_handler);
						$this->incoming_event_handler = null;
					}
				}
			}
		}
		$this->onStateChange();
		return $this;
	}

	public function unregisterOutgoingMonitor(Closure $handler) : IPacketMonitor{
		$hid = spl_object_id($handler);
		foreach($this->parsePidsFromHandler($handler, ClientboundPacket::class) as $pid){
			if(isset($this->outgoing_handlers[$pid][$hid])){
				unset($this->outgoing_handlers[$pid][$hid]);
				if(count($this->outgoing_handlers[$pid]) === 0){
					unset($this->outgoing_handlers[$pid]);
					if(count($this->outgoing_handlers) === 0){
						HandlerListManager::global()->getListFor(DataPacketSendEvent::class)->unregister($this->outgoing_event_handler);
						$this->outgoing_event_handler = null;
					}
				}
			}
		}
		$this->onStateChange();
		return $this;
	}
}