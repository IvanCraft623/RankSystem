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

namespace IvanCraft623\RankSystem\session;

use IvanCraft623\RankSystem\RankSystem;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use WeakMap;

final class SessionManager {
	use SingletonTrait;

	private RankSystem $plugin;

	/** @var WeakMap<Player, OnlineSession> */
	private WeakMap $onlineSessions;

	public function __construct() {
		$this->plugin = RankSystem::getInstance();
		$this->onlineSessions = new WeakMap();
	}

	public function get(Player|string $player) : Session {
		if ($player instanceof Player) {
			return $this->getOnline($player);
		}

		$onlinePlayer = $this->plugin->getServer()->getPlayerExact($player);
		if ($onlinePlayer !== null) {
			return $this->getOnline($onlinePlayer);
		}

		return new OfflineSession($player);
	}

	public function getOnline(Player $player) : OnlineSession {
		return $this->onlineSessions[$player] ??= new OnlineSession($player);
	}

	/**
	 * @return array<string, OnlineSession>
	 */
	public function getAll() : array {
		$sessions = [];
		foreach ($this->onlineSessions as $player => $session) {
			$sessions[$player->getName()] = $session;
		}
		return $sessions;
	}

	public function reload() : void {
		$replacement = [];
		foreach ($this->onlineSessions as $player => $session) {
			$session->close();
			$replacement[] = [$player, new OnlineSession($player)];
		}

		$this->onlineSessions = new WeakMap();
		foreach ($replacement as [$player, $session]) {
			$this->onlineSessions[$player] = $session;
		}
	}
}
