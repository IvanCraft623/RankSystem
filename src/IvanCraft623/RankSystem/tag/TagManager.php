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

namespace IvanCraft623\RankSystem\tag;

use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\session\Session;

use pocketmine\utils\SingletonTrait;
use function array_map;
use function implode;

final class TagManager {
	use SingletonTrait;

	/** @var Tag[] */
	private array $tags = [];

	public function registerTag(Tag $tag) : void {
		$this->tags[$tag->getId()] = $tag;
	}

	public function getTag(string $tagId) : ?Tag {
		return $this->tags[$tagId] ?? null;
	}

	/**
	 * @return Tag[]
	 */
	public function getTags() : array {
		return $this->tags;
	}

	/**
	 * @internal
	 */
	public function registerDefaults() : void {
		$this->registerTag(new Tag("name", static function(Session $user) : string {
			return $user->getName();
		}));
		$this->registerTag(new Tag("display_name", static function (Session $user) : string {
			return $user->getPlayer()?->getDisplayName() ?? "Error";
		}));
		$this->registerTag(new Tag("nametag_ranks_prefix", static function(Session $user) : string {
			return implode("", array_map(fn(Rank $rank) => $rank->getNameTagFormat()["prefix"], $user->getRanks()));
		}));
		$this->registerTag(new Tag("nametag_highest-rank_prefix", static function(Session $user) : string {
			return $user->getHighestRank()->getNameTagFormat()["prefix"];
		}));
		$this->registerTag(new Tag("nametag_name-color", static function(Session $user) : string {
			return $user->getHighestRank()->getNameTagFormat()["nameColor"];
		}));
		$this->registerTag(new Tag("chat_ranks_prefix", static function(Session $user) : string {
			return implode("", array_map(fn(Rank $rank) => $rank->getChatFormat()["prefix"], $user->getRanks()));
		}));
		$this->registerTag(new Tag("chat_highest-rank_prefix", static function(Session $user) : string {
			return $user->getHighestRank()->getChatFormat()["prefix"];
		}));
		$this->registerTag(new Tag("chat_name-color", static function(Session $user) : string {
			return $user->getHighestRank()->getChatFormat()["nameColor"];
		}));
		$this->registerTag(new Tag("chat_format", static function(Session $user) : string {
			return $user->getHighestRank()->getChatFormat()["chatFormat"];
		}));
	}
}