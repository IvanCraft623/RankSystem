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

namespace IvanCraft623\RankSystem\task;

use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\event\UserRankExpireEvent;

use pocketmine\scheduler\Task;

class UpdateTask extends Task {

	private RankSystem $plugin;

	public function __construct() {
		$this->plugin = RankSystem::getInstance();
	}

	public function onRun() : void {
		#Check Expired Ranks
		$time = time();
		foreach ($this->plugin->getSessionManager()->getAll() as $session) {
			foreach ($session->getTempRanks() as $rank) {
				if ($session->getRankExpTime($rank) <= $time) {
					# Call Event
					$ev = new UserRankExpireEvent(
						$session,
						$rank
					);
					$ev->call();

					$session->removeRank($rank);
				}
			}
			foreach ($session->getUserPermissions() as $permission) {
				$expTime = $session->getPermissionExpTime($permission);
				if ($session->isTempPermission($permission) && $expTime <= $time) {
					$session->removePermission($permission);
				}
			}
		}
	}
}