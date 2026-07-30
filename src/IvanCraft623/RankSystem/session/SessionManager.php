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

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

final class SessionManager {
	use SingletonTrait;

	/** @var array<string, Session> */
	private array $sessions = [];

	public function get(Player|string $player) : Session {
		$name = ($player instanceof Player) ? $player->getName() : $player;
		if (isset($this->sessions[$name])) {
			return $this->sessions[$name];
		}

		return $this->sessions[$name] = new Session($name);
	}

	/**
	 * @return array<string, Session>
	 */
	public function getAll() : array {
		return $this->sessions;
	}

	public function reload() : void {
		$sessions = [];
		foreach ($this->sessions as $user => $ss) {
			$sessions[$user] = new Session($user);
		}
		$this->sessions = $sessions;
	}
}