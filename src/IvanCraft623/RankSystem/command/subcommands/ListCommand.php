<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\subcommands;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\RankSystem;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class ListCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("list", "See the list of ranks");
		$this->setPermission("ranksystem.command.list");
	}

	protected function prepare() : void {
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$ranks = $this->plugin->getRankManager()->getAll();
		$sender->sendMessage("§a" . $this->plugin->getTranslator()->translate($sender, "text.ranks") . " (".count($ranks)."):");
		foreach ($ranks as $rank) {
			$sender->sendMessage("§f» §e".$rank->getName());
		}
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}