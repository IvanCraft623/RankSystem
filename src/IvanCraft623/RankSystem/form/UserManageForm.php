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

use IvanCraft623\RankSystem\session\Session;
use IvanCraft623\RankSystem\session\SessionManager;

use pocketmine\player\Player;

final class UserManageForm {
	
	public function __construct() {
	}

	public function send(Player $player) : void {
		$form = new SimpleForm(function (Player $player, int $result = null) {
			if ($result === null) {
				return;
			}
			switch ($result) {
				case 0:
					FormManager::getInstance()->sendInsertText(
						$player, "User Manager", "§7Insert a user.", "User:"
					)->onCompletion(
						function (string $user) use ($player) {
							FormManager::getInstance()->sendUserInfo($player, SessionManager::getInstance()->get($user), true);
						}, function () {} // No response
					);
					break;

				case 1:
					$sessions = [];
					foreach ($player->getServer()->getOnlinePlayers() as $pl) {
						$sessions[] = SessionManager::getInstance()->get($pl);
					}
					$this->sendSelectUserForm($player, $sessions);
					break;

				case 2:
					$this->sendSelectUserForm($player, SessionManager::getInstance()->getAll());
					break;
				
				default:
					# Close Form
					break;
			}
		});
		$form->setTitle("User Manager");
		$form->setContent("Select an action");
		$form->addButton("Insert user", SimpleForm::IMAGE_TYPE_PATH, "textures/ui/infobulb");
		$form->addButton("Online users", SimpleForm::IMAGE_TYPE_PATH, "textures/ui/World");
		$form->addButton("Loaded users", SimpleForm::IMAGE_TYPE_PATH, "textures/ui/icon_map");
		$form->addButton("Exit", SimpleForm::IMAGE_TYPE_PATH, "textures/blocks/barrier");
		$form->sendToPlayer($player);
	}

	/**
	 * @param Session[] $sessions
	 */
	public function sendSelectUserForm(Player $player, array $sessions) : void {
		$form = new SimpleForm(function (Player $player, Session $session = null) {
			if ($session === null) {
				return;
			}
			FormManager::getInstance()->sendUserInfo($player, $session, true);
		});
		$form->setTitle("User Manager");
		$form->setContent("Select an user.");
		foreach ($sessions as $session) {
			$form->addButton($session->getName(), -1, "", $session);
		}
		$form->sendToPlayer($player);
	}
}