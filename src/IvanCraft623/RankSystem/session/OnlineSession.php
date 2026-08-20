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

use IvanCraft623\RankSystem\utils\Utils;

use pocketmine\permission\PermissionAttachment;
use pocketmine\player\Player;
use WeakReference;
use function array_fill_keys;

final class OnlineSession extends Session {

	/** @var WeakReference<Player> */
	private WeakReference $playerRef;

	private ?PermissionAttachment $attachment = null;

	private SessionChatFormatter $chatFormatter;

	public function __construct(Player $player) {
		parent::__construct($player->getName());
		$this->playerRef = WeakReference::create($player);
		$this->chatFormatter = new SessionChatFormatter($this);
		$this->attachment = $player->addAttachment($this->plugin);
	}

	public function getPlayer() : ?Player {
		$player = $this->playerRef->get();
		return $player instanceof Player ? $player : null;
	}

	public function getChatFormatter() : SessionChatFormatter {
		return $this->chatFormatter;
	}

	public function updateRanks() : void {
		parent::updateRanks();

		$player = $this->playerRef->get();
		if ($player instanceof Player) {
			$this->updatePermissions();
			$this->updateNameTag();
			Utils::updateScoreTags($this);
		}
	}

	/**
	 * @internal
	 */
	public function close() : void {
		$player = $this->playerRef->get();
		if ($this->attachment !== null && $player instanceof Player) {
			$player->removeAttachment($this->attachment);
		}
		$this->attachment = null;
	}

	private function updatePermissions() : void {
		$this->attachment?->setPermissions(array_fill_keys($this->permissions, true));
	}

	private function updateNameTag() : void {
		$player = $this->playerRef->get();
		if ($player instanceof Player && $this->plugin->getConfig()->getNested("nametag.enabled", true)) {
			$player->setNameTag($this->getNameTagFormat());
		}
	}
}
