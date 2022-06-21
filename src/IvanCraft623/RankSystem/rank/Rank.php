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

final class Rank {

	private string $name;

	private array $nametag = [];

	private array $chat = [];

	private array $permissions = [];

	/** @var Rank[] */
	private array $inheritance = [];

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
		$this->name = $name;
		$this->nametag = $nametag;
		$this->chat = $chat;
		$this->permissions = $permissions;
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

	public function getInheritance() : array {
		return $this->inheritance;
	}

	/**
	 * @internal
	 */
	public function addInheritance(Rank $rank) : void {
		if ($rank === $this) {
			throw new \InvalidArgumentException("A rank cannot inherit ranks from itself");
		}

		$this->inheritance[] = $rank;
		$this->permissions = array_merge($this->permissions, $rank->getPermissions());
	}
}