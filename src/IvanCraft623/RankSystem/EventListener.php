<?php

#Plugin By:

/*
	8888888                            .d8888b.                   .d888 888     .d8888b.   .d8888b.   .d8888b.  
	  888                             d88P  Y88b                 d88P"  888    d88P  Y88b d88P  Y88b d88P  Y88b 
	  888                             888    888                 888    888    888               888      .d88P 
	  888  888  888  8888b.  88888b.  888        888d888 8888b.  888888 888888 888d888b.       .d88P     8888"  
	  888  888  888     "88b 888 "88b 888        888P"      "88b 888    888    888P "Y88b  .od888P"       "Y8b. 
	  888  Y88  88P .d888888 888  888 888    888 888    .d888888 888    888    888    888 d88P"      888    888 
	  888   Y8bd8P  888  888 888  888 Y88b  d88P 888    888  888 888    Y88b.  Y88b  d88P 888"       Y88b  d88P 
	8888888  Y88P   "Y888888 888  888  "Y8888P"  888    "Y888888 888     "Y888  "Y8888P"  888888888   "Y8888P"  
*/

declare(strict_types=1);

namespace IvanCraft623\RankSystem;

use IvanCraft623\RankSystem\{RankSystem as Ranks, event\UserRankExpireEvent};

use pocketmine\{Server, player\Player};
use pocketmine\event\{Listener, player\PlayerJoinEvent, player\PlayerChatEvent};

class EventListener implements Listener {

	/**
	 * @priority HIGH
	 */
	public function onJoin(PlayerJoinEvent $event) : void {
		$player = $event->getPlayer();
		$session = Ranks::getInstance()->getSessionManager()->get($player->getName());
		$session->updateRanks();
	}

	/**
	 * @priority HIGH
	 */
	public function onChat(PlayerChatEvent $event) : void {
		if ($event->isCancelled()) return;
		$player = $event->getPlayer();
		$session = Ranks::getInstance()->getSessionManager()->get($player->getName());
		$event->setFormat($session->getChatFormat().$event->getMessage());
	}

	/**
	 * @priority HIGH
	 */
	public function onRankExpire(UserRankExpireEvent $event) : void {
		$session = $event->getSession();
		$rank = $event->getRank();
		$player = Ranks::getInstance()->getServer()->getPlayerByPrefix($session->getName());
		if ($player !== null) {
			$player->sendMessage("§c» §eYour §b".$rank->getName()."§e rank has expired!");
		}
	}
}