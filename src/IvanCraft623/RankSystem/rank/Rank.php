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

namespace IvanCraft623\RankSystem\rank;

use IvanCraft623\RankSystem\RankSystem as Ranks;

final class Rank {

	/** @var Array */
	private static $ranks = [];

	/** @var Ranks */
	private $plugin;

	/** @var String */
	private $name;

	/** @var Array */
	private $nametag = [];

	/** @var Array */
	private $chat = [];

	/** @var String[] */
	private $permissions = [];

	public static function getByName(string $name) : ?Rank {
		foreach (self::$ranks as $nm => $rank) {
			if ($nm === $name) {
				return $rank;
			}
		}
		return null;
	}

	public static function closeAll() : void {
		self::$ranks = [];
	}

	public static function getAll() : array {
		return self::$ranks;
	}

	/* Example of how provide the variables:
	 * 
	 * $nametag = [
	 *		"prefix" => "§2[§aCat§2] ",
	 *		"nameColor" => "§6"
	 * ];
	 *
	 * $chat = [
	 *		"prefix" => "§2[§aCat§2] ",
	 *		"nameColor" => "§6",
	 *		"chatFormat" => "§5: §b"
	 * ];
	 *
	 * $perms = ["example.perm", "example.perm2"]:
	 */
	public function __construct(string $name, array $nametag, array $chat, array $permissions = []) {
		$this->plugin = Ranks::getInstance();
		$this->name = $name;
		$this->nametag = $nametag;
		$this->chat = $chat;
		$this->permissions = $permissions;

		self::$ranks[$name] = $this;
	}

	public function getName() : string {
		return $this->name;
	}

	public function getNameTagFormat() : array {
		return $this->nametag;
	}

	public function getChatFormat() : array {
		return $this->chat;
	}

	public function getPermissions() : array {
		return $this->permissions;
	}
}