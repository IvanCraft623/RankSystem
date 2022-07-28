<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\form;

use IvanCraft623\RankSystem\rank\Rank;

use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\utils\SingletonTrait;

final class FormManager {
	use SingletonTrait;

	public function sendManager(Player $player) : void {
		(new ManageForm())->send($player);
	}

	public function sendRanksManager(Player $player) : void {
		(new RanksManageForm())->send($player);
	}

	public function sendUserManager(Player $player) : void {
		(new UserManageForm())->send($player);
	}

	public function sendRankEditor(Player $player, string $name, array $nametag = ["prefix" => "", "nameColor" => "§f"], array $chat = ["prefix" => "", "nameColor" => "§f", "chatFormat" => "§e: §7"], array $permissions = [], array $inheritance = []) : void {
		(new RankEditorForm($name, $nametag, $chat, $permissions, $inheritance))->send($player);
	}

	public function sendRankInfo(Player $player, Rank $rank) : void {
		(new RankInfoForm())->send($player, $rank);
	}

	public function sendInsertText(Player $player, string $title, string $content, string $text, string $placeholder = "", ?string $default = null) : Promise {
		return (new InsetTextForm())->send($player, $title, $content, $text, $placeholder, $default);
	}

	public function sendSelectRank(Player $player, string $title) : Promise {
		return (new SelectRankForm())->send($player, $title);
	}

	public function sendConfirmation(Player $player, string $title, string $content) : Promise {
		return (new ConfirmationForm())->send($player, $title, $content);
	}
}