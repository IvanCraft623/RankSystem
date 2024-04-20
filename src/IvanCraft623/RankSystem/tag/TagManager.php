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

namespace IvanCraft623\RankSystem\tag;

use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\session\Session;

use pocketmine\utils\SingletonTrait;

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
		$this->registerTag(new Tag("display_name", static function (Session $user): string {
			return $user->getPlayer()->getDisplayName();
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