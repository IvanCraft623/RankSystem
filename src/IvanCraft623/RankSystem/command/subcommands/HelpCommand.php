<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\subcommands;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class HelpCommand extends BaseSubCommand {

	public function __construct() {
		parent::__construct("help", "See RankSystem command", ["?"]);
		$this->setPermission("ranksystem.command.help");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new IntegerArgument("page", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$avaiable = [];
		foreach ($this->parent->getSubCommands() as $subCommand) {
			$id = spl_object_id($subCommand);
			if (!isset($avaiable[$id]) && $subCommand->testPermissionSilent($sender)) {
				$avaiable[$id] = $subCommand;
			}
		}
		$pageHeight = $sender instanceof Player ? 6 : 48;
		$chunkedCommands = array_chunk($avaiable, $pageHeight);
		$maxPageNumber = count($chunkedCommands);
		if (!isset($args["page"]) || $args["page"] <= 0) {
			$pageNumber = 1;
		} elseif ($args["page"] > $maxPageNumber) {
			$pageNumber = $maxPageNumber;
		} else {
			$pageNumber = $args["page"];
		}
		$sender->sendMessage("§2--- Showing RankSystem help page " . $pageNumber . " of " . $maxPageNumber . " (/ranksystem help <page>) ---");
		foreach ($chunkedCommands[$pageNumber - 1] as $subCommand) {
			$sender->sendMessage("/ranksystem " . $subCommand->getName() . " §7(" . $subCommand->getDescription() . ")");
		}
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}