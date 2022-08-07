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
			$translator = $this->plugin->getTranslator();
			if (!$session->hasRank($args["rank"])) {
				$sender->sendMessage($translator->translate($sender, "user.remove_rank.no_rank", [
					"{%user}" => $session->getName(),
					"{%rank}" => $args["rank"]->getName()
				]));
			} else {
				$session->removeRank($args["rank"]);
				$sender->sendMessage($translator->translate($sender, "user.remove_rank.success", [
					"{%user}" => $session->getName(),
					"{%rank}" => $args["rank"]->getName()
				]));
			}
		});
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}