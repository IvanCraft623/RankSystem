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

namespace IvanCraft623\RankSystem\form;

use jojoe77777\FormAPI\SimpleForm;

use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\rank\RankManager;
use IvanCraft623\RankSystem\session\Session;
use IvanCraft623\RankSystem\utils\Utils;

use pocketmine\player\Player;

final class UserInfoForm {
	
	public function __construct() {
	}

	public function send(Player $player, Session $session, bool $manage = false) : void {
		$session->onInitialize(function () use ($player, $session, $manage) {
			$form = new SimpleForm(function (Player $player, int $result = null) use ($session) {
				if ($result === null) {
					return;
				}
				switch ($result) {
					case 0:
						$ranks = [];
						foreach (RankManager::getInstance()->getAll() as $rank) {
							if (!$session->hasRank($rank)) $ranks[] = $rank;
						}
						FormManager::getInstance()->sendSelectRank($player, "Set rank", $ranks)->onCompletion(
							function (Rank $rank) use ($player, $session) {
								FormManager::getInstance()->sendConfirmation($player, "Set rank", "Do you want the rank to expire?")->onCompletion(
									function (bool $expire) use ($player, $session, $rank) {
										if ($expire) {
											FormManager::getInstance()->sendInsertTime($player, "Set rank", "§7The rank will expire after this time has elapsed.")->onCompletion(
												function (int $time) use ($player, $session, $rank) {
													$session->setRank($rank, $time + time());
													$player->sendMessage(
														"§a---- §6You have given a Rank! §a----"."\n"."\n".
														"§eUser:§b {$session->getName()}"."\n".
														"§eRank:§b {$rank->getName()}"."\n".
														"§eExpire In:§b " . Utils::getTimeTranslated($time)
													);
												}, function () {} // No response
											);
										} else {
											$session->setRank($rank);
											$player->sendMessage(
												"§a---- §6You have given a Rank! §a----"."\n"."\n".
												"§eUser:§b {$session->getName()}"."\n".
												"§eRank:§b {$rank->getName()}"."\n".
												"§eExpire In:§b Never"
											);
										}
									}, function () {} // No response
								);
							}, function () {} // No response
						);
						break;

					case 1:
						FormManager::getInstance()->sendSelectRank($player, "Remove rank", $session->getRanks())->onCompletion(
							function (Rank $rank) use ($player, $session) {
								FormManager::getInstance()->sendConfirmation(
									$player, "Remove rank",
									"Do you want to §cremove §r" . $session->getName() . "'s " . $rank->getName() . " rank?"
								)->onCompletion(
									function (bool $remove) use ($player, $session, $rank) {
										$session->removeRank($rank);
										$player->sendMessage("§bYou have successfully §cremoved§b §e" . $session->getName() . "§b's §a" . $rank->getName() . " §brank");
									}, function () {} // No response
								);
							}, function () {} // No response
						);
						break;

					case 2:
						FormManager::getInstance()->sendInsertText($player, "Set permission", "§7Write permission", "Permission:")->onCompletion(
							function (string $permission) use ($player, $session) {
								if ($session->hasUserPermission($permission)) {
									$player->sendMessage("§c" . $session->getName() . " already has the " . $permission . " permission!");
								} else {
									FormManager::getInstance()->sendConfirmation($player, "Set permission", "Do you want the permission to expire?")->onCompletion(
										function (bool $expire) use ($player, $session, $permission) {
											if ($expire) {
												FormManager::getInstance()->sendInsertTime($player, "Set permission", "§7The permission will expire after this time has elapsed.")->onCompletion(
													function (int $time) use ($player, $session, $permission) {
														$session->setPermission($permission, $time + time());
														$player->sendMessage(
															"§a---- §6You have given a Permission! §a----"."\n"."\n".
															"§eUser:§b {$session->getName()}"."\n".
															"§ePermission:§b {$permission}"."\n".
															"§eExpire In:§b " . Utils::getTimeTranslated($time)
														);
													}, function () {} // No response
												);
											} else {
												$session->setPermission($permission);
												$player->sendMessage(
													"§a---- §6You have given a Permission! §a----"."\n"."\n".
													"§eUser:§b {$session->getName()}"."\n".
													"§ePermission:§b {$permission}"."\n".
													"§eExpire In:§b Never"
												);
											}
										}, function () {} // No response
									);
								}
							}, function () {} // No response
						);
						break;

					case 3:
						FormManager::getInstance()->sendInsertText($player, "Remove permission", "§7Write permission", "Permission:")->onCompletion(
							function (string $permission) use ($player, $session) {
								if (!$session->hasUserPermission($permission)) {
									$sender->sendMessage("§c" . $session->getName() . " does not has the " . $permission . " permission!");
									return;
								}
								FormManager::getInstance()->sendConfirmation(
									$player, "Remove permission",
									"Do you want to §cremove §r" . $session->getName() . "'s " . $permission . " permission?"
								)->onCompletion(
									function (bool $remove) use ($player, $session, $permission) {
										$session->removePermission($permission);
										$player->sendMessage("§bYou have successfully §cremoved§b the §e" . $permission . " §bpermission from §a". $session->getName());
									}, function () {} // No response
								);
							}, function () {} // No response
						);
						break;
					
					default:
						# Close Form
						break;
				}
			});
			$form->setTitle("User Information");
			$permissions = "";
			foreach ($session->getUserPermissions() as $permission) {
				$time = $session->getPermissionExpTime($permission);
				if ($time !== null) {
					$time = $time - time();
					if ($time < 0) {
						$time = null;
					}
				}
				$permissions .= "\n §e - " . $permission . " §7(" . ($time === null ? "Never" : Utils::getTimeTranslated($time)) . ")";
			}
			$ranks = "";
			foreach ($session->getRanks() as $rank) {
				$time = $session->getRankExpTime($rank);
				if ($time !== null) {
					$time = $time - time();
					if ($time < 0) {
						$time = null;
					}
				}
				$ranks .= "\n §e - " . $rank->getName() . " §7(" . ($time === null ? "Never" : Utils::getTimeTranslated($time)) . ")";
			}
			$form->setContent(
				"§r§fUser: §a" . $session->getName() . "\n\n" .
				"§r§fNametag: " . $session->getNameTagFormat() . "\n" .
				"§r§fChat: " . $session->getChatFormat() . "Hello world!" . "\n\n" .
				"§r§fRanks: " . $ranks . "\n" .
				"§r§fPermissions: §a" . $permissions
			);
			if ($manage) {
				$form->addButton("Set rank", SimpleForm::IMAGE_TYPE_PATH, "textures/ui/book_edit_default");
				$form->addButton("Remove rank", SimpleForm::IMAGE_TYPE_PATH, "textures/ui/book_edit_default");
				$form->addButton("Set permission", SimpleForm::IMAGE_TYPE_PATH, "textures/ui/book_edit_default");
				$form->addButton("Remove permission", SimpleForm::IMAGE_TYPE_PATH, "textures/ui/book_edit_default");
			}
			$form->sendToPlayer($player);
		});
	}
}