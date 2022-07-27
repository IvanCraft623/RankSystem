<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\subcommands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\command\args\TimeArgument;
use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\utils\Utils;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;

final class SetPermissionCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("setpermission", "Set a permissions to a user", ["setperm"]);
		$this->setPermission("ranksystem.command.setpermission");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new RawStringArgument("user"));
		$this->registerArgument(1, new RawStringArgument("permission"));
		$this->registerArgument(2, new TimeArgument("time", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		if (array_key_exists("time", $args) && $args["time"] === null) {
			$sender->sendMessage(
				"§cInvalid time provided!"."\n".
				"§aDuration arguments: y = year, M = month, w = week, d = day, h = hour, m = minute"."\n".
				"§eFor instance, 1y3M means one year and three months (this is the same as 15M). 1w2d12h means one week, two days, and twelve hours (this is the same as 9d12h)."
			);
		} else {
			$session = $this->plugin->getSessionManager()->get($args["user"]);
			$session->onInitialize(function () use ($session, $sender, $args) {
				if ($session->hasPermission($args["permission"])) {
					$sender->sendMessage("§c" . $args["user"] . " already has the " . $args["permission"] . " permission!");
				} else {
					$session->setPermission($args["permission"], $args["time"] ?? null);
					$sender->sendMessage(
						"§a---- §6You have given a Permission! §a----"."\n"."\n".
						"§eUser:§b {$args["user"]}"."\n".
						"§ePermission:§b {$args["permission"]}"."\n".
						"§eExpire In:§b " . (isset($args["time"]) ? Utils::getTimeTranslated($args["time"] - time()) : "Never")
					);
				}
			});
		}
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}