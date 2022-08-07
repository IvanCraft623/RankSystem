<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;

use IvanCraft623\RankSystem\RankSystem;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class CreateCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("create", "Create a Rank");
		$this->setPermission("ranksystem.command.create");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new RawStringArgument("rank"));
		$this->addConstraint(new InGameRequiredConstraint($this));
	}

	/**
	 * @param Player $sender
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		if ($this->plugin->getRankManager()->exists($args["rank"])) {
			$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "rank.already_exists", [
				"{%rank}" => $args["rank"]
			]));
		} else {
			$this->plugin->getFormManager()->sendRankEditor(
				$sender,
				$args["rank"],
				["prefix" => "§8[§7" . $args["rank"] . "§8] ", "nameColor" => "§f"],
				["prefix" => "§8[§7" . $args["rank"] . "§8] ", "nameColor" => "§f", "chatFormat" => "§e: §7"]
			);
		}
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}