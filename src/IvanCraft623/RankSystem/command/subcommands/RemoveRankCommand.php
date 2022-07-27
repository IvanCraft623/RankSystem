<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\command\args\RankArgument;
use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\rank\Rank;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class RemoveRankCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("removerank", "Remove a rank from a user", ["remove"]);
		$this->setPermission("ranksystem.command.removerank");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new RawStringArgument("user"));
		$this->registerArgument(1, new RankArgument("rank"));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$session = $this->plugin->getSessionManager()->get($args["user"]);
		$session->onInitialize(function () use ($session, $sender, $args) {
			if (!$session->hasRank($args["rank"])) {
				$sender->sendMessage("§c" . $args["user"] . " does not has the " . $args["rank"]->getName() . " rank!");
			} else {
				$session->removeRank($args["rank"]);
				$sender->sendMessage("§bYou have successfully §cremoved§b the §e" . $args["rank"]->getName() . " §brank from §a". $args["user"]);
			}
		});
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}