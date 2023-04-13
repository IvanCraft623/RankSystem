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

namespace IvanCraft623\RankSystem\session;

use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

final class SessionManager {
	use SingletonTrait;

	private array $sessions = [];

	public function get(Player|string $player) : Session {
		$player = ($player instanceof Player) ? $player->getName() : $player;
		if (isset($this->sessions[mb_strtolower($player)])) {
			return $this->sessions[mb_strtolower($player)];
		}
		$session = new Session($player);
		$this->sessions[$player] = $session;
		return $session;
	}
	
	public function contains(Player|string $player): bool{
		return isset($this->sessions[mb_strtolower(($player instanceof Player) ? $player->getName() : $player)]);
	}

	public function getAll() : array {
		return $this->sessions;
	}

	public function reload() {
		$sessions = [];
		foreach ($this->sessions as $user => $ss) {
			$sessions[mb_strtolower($user)] = new Session($user);
		}
		$this->sessions = $sessions;
	}
}
