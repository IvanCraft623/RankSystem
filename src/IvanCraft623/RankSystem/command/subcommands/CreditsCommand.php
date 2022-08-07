<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\subcommands;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\RankSystem;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class CreditsCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("credits", "See RankSystem credits");
		$this->setPermission("ranksystem.command.credits");
	}

	protected function prepare() : void {
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$translator = $this->plugin->getTranslator();
		$sender->sendMessage(
			"§a---- §6" . $this->plugin->getName() . " §b" . $translator->translate($sender, "text.credits") . " §a----"."\n"."\n".
			"§e" . $translator->translate($sender, "text.author") . ": §7IvanCraft623 / IvanCraft236"."\n".
			"§e" . $translator->translate($sender, "text.website") . ": §7https://poggit.pmmp.io/p/RankSystem"."\n"."\n".
			$translator->translate($sender, "credits.text")
		);
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}