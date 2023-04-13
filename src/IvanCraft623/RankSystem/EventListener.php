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

use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\event\UserRankExpireEvent;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;

class EventListener implements Listener {

	private RankSystem $plugin;

	public function __construct() {
		$this->plugin = RankSystem::getInstance();
	}

	/**
	 * @priority LOW
	 */
	public function onJoin(PlayerJoinEvent $event) : void {
		$player = $event->getPlayer();
		$alreadyExists = $this->plugin->getSessionManager()->contains($player);
		$session = $this->plugin->getSessionManager()->get($player);
		$session->setPlayer($player);
		$session->onInitialize(function () use ($session, $alreadyExists) {
			if ($alreadyExists) $session->loadUserData();
			else $session->updateRanks();
		});
	}

	/**
	 * @priority LOW
	 */
	public function onPreLogin(PlayerPreLoginEvent $event) : void {
		// This is to have the session ready in case a plugin wants to get data
		$this->plugin->getSessionManager()->get($event->getPlayerInfo()->getUsername());
	}

	/**
	 * @priority HIGH
	 * @ignoreCancelled
	 */
	public function onChat(PlayerChatEvent $event) : void {
		if ($this->plugin->getConfig()->getNested("chat.enabled", true)) {
			$player = $event->getPlayer();
			$session = $this->plugin->getSessionManager()->get($player);
			$event->setFormat(str_replace("{message}", $event->getMessage(), $session->getChatFormat()));
		}
	}

	/**
	 * @priority HIGH
	 */
	public function onRankExpire(UserRankExpireEvent $event) : void {
		if (!((bool) $this->plugin->getConfig()->get("rank-expire-notification", true))) return;
		$session = $event->getSession();
		$player = $session->getPlayer();
		if ($player !== null && $player->isOnline()) {
			$player->sendMessage($this->plugin->getTranslator()->translate($player, "user.rank.expire", [
				"{%rank}" => $event->getRank()->getName()
			]));
		}
	}
}
