<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\subcommands;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class CreditsCommand extends BaseSubCommand {

	public function __construct() {
		parent::__construct("credits", "See RankSystem credits");
		$this->setPermission("ranksystem.command.credits");
	}

	protected function prepare() : void {
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$sender->sendMessage(
			"§a---- §6RankSystem §bCredits §a----"."\n"."\n".
			"§eAuthor: §7IvanCraft623 / IvanCraft236"."\n".
			"§eWebsite: §7https://poggit.pmmp.io/p/RankSystem"."\n"."\n".
			"§bMade with love! :D"
		);
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}