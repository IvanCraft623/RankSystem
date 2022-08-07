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

use IvanCraft623\languages\Translator;
use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\rank\RankManager;
use IvanCraft623\RankSystem\session\Session;
use IvanCraft623\RankSystem\utils\Utils;

use pocketmine\player\Player;

final class UserInfoForm {

	private Translator $translator;
	
	public function __construct() {
		$this->translator = RankSystem::getInstance()->getTranslator();
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
						FormManager::getInstance()->sendSelectRank($player, $this->translator->translate($player, "form.set_rank.title"), $ranks)->onCompletion(
							function (Rank $rank) use ($player, $session) {
								FormManager::getInstance()->sendConfirmation($player, $this->translator->translate($player, "form.set_rank.title"), $this->translator->translate($player, "form.set_rank.expire"))->onCompletion(
									function (bool $expire) use ($player, $session, $rank) {
										if ($expire) {
											FormManager::getInstance()->sendInsertTime($player, $this->translator->translate($player, "form.set_rank.title"), $this->translator->translate($player, "form.set_rank.expire.content"))->onCompletion(
												function (int $time) use ($player, $session, $rank) {
													$session->setRank($rank, $time + time());
													$player->sendMessage($this->translator->translate($player, "user.set_rank.success", [
														"{%user}" => $session->getName(),
														"{%rank}" => $rank->getName(),
														"{%time}" => Utils::getTimeTranslated($time, $this->translator, $player)
													]));
												}, function () {} // No response
											);
										} else {
											$session->setRank($rank);
											$player->sendMessage($this->translator->translate($player, "user.set_rank.success", [
												"{%user}" => $session->getName(),
												"{%rank}" => $rank->getName(),
												"{%time}" => $this->translator->translate($player, "text.never")
											]));
										}
									}, function () {} // No response
								);
							}, function () {} // No response
						);
						break;

					case 1:
						FormManager::getInstance()->sendSelectRank($player, $this->translator->translate($player, "form.remove_rank.title"), $session->getRanks())->onCompletion(
							function (Rank $rank) use ($player, $session) {
								FormManager::getInstance()->sendConfirmation(
									$player, $this->translator->translate($player, "form.remove_rank.title"),
									$this->translator->translate($player, "form.remove_rank.confirm", [
										"{%user}" => $session->getName(),
										"{%rank}" => $rank->getName()
									])
								)->onCompletion(
									function (bool $remove) use ($player, $session, $rank) {
										$session->removeRank($rank);
										$player->sendMessage($this->translator->translate($player, "user.remove_rank.success", [
											"{%user}" => $session->getName(),
											"{%rank}" => $rank->getName()
										]));
									}, function () {} // No response
								);
							}, function () {} // No response
						);
						break;

					case 2:
						FormManager::getInstance()->sendInsertText($player, $this->translator->translate($player, "form.set_permission.title"), $this->translator->translate($player, "form.set_permission.content"), $this->translator->translate($player, "text.permission") . ":")->onCompletion(
							function (string $permission) use ($player, $session) {
								if ($session->hasUserPermission($permission)) {
									$player->sendMessage($this->translator->translate($player, "user.set_permission.already_has", [
										"{%user}" => $session->getName(),
										"{%permission}" => $permission
									]));
								} else {
									FormManager::getInstance()->sendConfirmation($player, $this->translator->translate($player, "form.set_permission.title"), $this->translator->translate($player, "form.set_permission.expire"))->onCompletion(
										function (bool $expire) use ($player, $session, $permission) {
											if ($expire) {
												FormManager::getInstance()->sendInsertTime($player, $this->translator->translate($player, "form.set_permission.title"), $this->translator->translate($player, "form.set_permission.expire.content"))->onCompletion(
													function (int $time) use ($player, $session, $permission) {
														$session->setPermission($permission, $time + time());
														$player->sendMessage($this->translator->translate($player, "user.set_permission.success", [
															"{%user}" => $session->getName(),
															"{%permission}" => $permission,
															"{%time}" => Utils::getTimeTranslated($time, $this->translator, $player)
														]));
													}, function () {} // No response
												);
											} else {
												$session->setPermission($permission);
												$player->sendMessage($this->translator->translate($player, "user.set_permission.success", [
													"{%user}" => $session->getName(),
													"{%permission}" => $permission,
													"{%time}" => $this->translator->translate($player, "text.never")
												]));
											}
										}, function () {} // No response
									);
								}
							}, function () {} // No response
						);
						break;

					case 3:
						FormManager::getInstance()->sendInsertText($player, $this->translator->translate($player, "form.remove_permission.title"), $this->translator->translate($player, "form.remove_permission.content"), $this->translator->translate($player, "text.permission") . ":")->onCompletion(
							function (string $permission) use ($player, $session) {
								if (!$session->hasUserPermission($permission)) {
									$player->sendMessage($this->translator->translate($player, "user.remove_permission.no_permission", [
										"{%user}" => $session->getName(),
										"{%permission}" => $permission
									]));
									return;
								}
								FormManager::getInstance()->sendConfirmation(
									$player, $this->translator->translate($player, "form.remove_permission.title"),
									$this->translator->translate($player, "user.remove_permission.confirm", [
										"{%user}" => $session->getName(),
										"{%permission}" => $permission
									])
								)->onCompletion(
									function (bool $remove) use ($player, $session, $permission) {
										$session->removePermission($permission);
										$player->sendMessage($this->translator->translate($player, "user.remove_permission.success", [
											"{%user}" => $session->getName(),
											"{%permission}" => $permission
										]));
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
			$form->setTitle($this->translator->translate($player, "form.user_info.title"));
			$permissions = "";
			foreach ($session->getUserPermissions() as $permission) {
				$time = $session->getPermissionExpTime($permission);
				if ($time !== null) {
					$time = $time - time();
					if ($time < 0) {
						$time = null;
					}
				}
				$permissions .= "\n §e - " . $permission . " §7(" . ($time === null ? $this->translator->translate($player, "text.never") : Utils::getTimeTranslated($time, $this->translator, $player)) . ")";
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
				$ranks .= "\n §e - " . $rank->getName() . " §7(" . ($time === null ? $this->translator->translate($player, "text.never") : Utils::getTimeTranslated($time, $this->translator, $player)) . ")";
			}
			$form->setContent(
				"§r§f" . $this->translator->translate($player, "text.user") . ": §a" . $session->getName() . "\n\n" .
				"§r§f" . $this->translator->translate($player, "text.nametag") . ": " . $session->getNameTagFormat() . "\n" .
				"§r§f" . $this->translator->translate($player, "text.chat") . ": " . $session->getChatFormat() . $this->translator->translate($player, "text.hello_world") . "\n\n" .
				"§r§f" . $this->translator->translate($player, "text.ranks") . ": " . $ranks . "\n" .
				"§r§f" . $this->translator->translate($player, "text.permissions") . ": §a" . $permissions
			);
			if ($manage) {
				$form->addButton($this->translator->translate($player, "form.set_rank.title"), SimpleForm::IMAGE_TYPE_PATH, "textures/ui/book_edit_default");
				$form->addButton($this->translator->translate($player, "form.remove_rank.title"), SimpleForm::IMAGE_TYPE_PATH, "textures/ui/book_edit_default");
				$form->addButton($this->translator->translate($player, "form.set_permission.title"), SimpleForm::IMAGE_TYPE_PATH, "textures/ui/book_edit_default");
				$form->addButton($this->translator->translate($player, "form.remove_permission.title"), SimpleForm::IMAGE_TYPE_PATH, "textures/ui/book_edit_default");
			}
			$form->sendToPlayer($player);
		});
	}
}