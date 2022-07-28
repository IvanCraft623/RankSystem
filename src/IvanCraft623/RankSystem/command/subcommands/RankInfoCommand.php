<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\subcommands;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\command\args\RankArgument;
use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\utils\Utils;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class RankInfoCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("rankinfo", "Shows info about a rank");
		$this->setPermission("ranksystem.command.rankinfo");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new RankArgument("rank"));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		if ($sender instanceof Player) {
			$this->plugin->getFormManager()->sendRankInfo($sender, $args["rank"]);
		} else {
			$nametag = $args["rank"]->getNameTagFormat();
			$chat = $args["rank"]->getChatFormat();
			$permissions = "";
			foreach ($args["rank"]->getPermissions() as $permission) {
				$permissions .= "\n §e - " . $permission;
			}
			$sender->sendMessage(
				"§r§fRank: §a" . $args["rank"]->getName() . "\n\n" .
				"§r§fNametag: " . $nametag["prefix"] . $nametag["nameColor"] . "Steve" . "\n" .
				"§r§fChat: " . $chat["prefix"] . $chat["nameColor"] . "Steve".$chat["chatFormat"] . "Hello world!" . "\n" 	.
				"§r§fInheritance: §a" . Utils::ranks2string($args["rank"]->getInheritance()) . "\n" .
				"§r§fPermissions: §a" . $permissions
			);
		}
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}