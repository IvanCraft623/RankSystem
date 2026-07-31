<?php

/*
 *   ____             _     ____
 *  |  _ \ __ _ _ __ | | __/ ___| _   _ ___| |_ ___ _ __ ___
 *  | |_) / _` | '_ \| |/ /\___ \| | | / __| __/ _ \ '_ ` _ \
 *  |  _ < (_| | | | |   <  ___) | |_| \__ \ ||  __/ | | | | |
 *  |_| \_\__,_|_| |_|_|\_\|____/ \__, |___/\__\___|_| |_| |_|
 *                                |___/
 *
 * An amazing rank and permissions manager for PocketMine-MP.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author IvanCraft623
 */

declare(strict_types=1);

namespace IvanCraft623\RankSystem;

use IvanCraft623\RankSystem\event\UserRankExpireEvent;

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
		$session = $this->plugin->getSessionManager()->get($player);
		$session->setPlayer($player);
		$session->onInitialize(function () use ($session) {
			$session->updateRanks();
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
			$event->setFormatter($session->getChatFormatter());
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
