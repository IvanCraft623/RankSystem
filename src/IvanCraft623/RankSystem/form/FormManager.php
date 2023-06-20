<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\form;

use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\session\Session;

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

	public function sendUserInfo(Player $player, Session $session, bool $manage = false) : void {
		(new UserInfoForm())->send($player, $session, $manage);
	}

	/**
	 * @phpstan-return Promise<string>
	 */
	public function sendInsertText(Player $player, string $title, string $content, string $text, string $placeholder = "", ?string $default = null) : Promise {
		return (new InsetTextForm())->send($player, $title, $content, $text, $placeholder, $default);
	}

	/**
	 * @phpstan-return Promise<int>
	 */
	public function sendInsertTime(Player $player, string $title, string $content) : Promise {
		return (new InsetTimeForm())->send($player, $title, $content);
	}

	/**
	 * @param ?Rank[] $ranks
	 *
	 * @phpstan-return Promise<Rank>
	 */
	public function sendSelectRank(Player $player, string $title, ?array $ranks = null) : Promise {
		return (new SelectRankForm())->send($player, $title, $ranks);
	}

	/**
	 * @phpstan-return Promise<bool>
	 */
	public function sendConfirmation(Player $player, string $title, string $content) : Promise {
		return (new ConfirmationForm())->send($player, $title, $content);
	}
}