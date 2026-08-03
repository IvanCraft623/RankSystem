<?php

/*
 *   ____             _     ____
 *  |  _ \ __ _ _ __ | | __/ ___| _   _ ___| |_ ___ _ __ ___
 *  | |_) / _` | '_ \| |/ /\___ \| | | / __| __/ _ \ '_ ` _ \
 *  |  _ < (_| | | | |   <  ___) | |_| \__ \ ||  __/ | | | | |
 *  |_| \_\__,_|_| |_|_|\_\|____/ \__, |___/\__\___|_| |_| |_|
 *                                |___/
 *
 * An amazing rank and permissions manager for PocketMine-MP.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author IvanCraft623
 */

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command\subcommands;

use IvanCraft623\RankSystem\libs\_3363d0a4024b663e\CortexPE\Commando\args\RawStringArgument;
use IvanCraft623\RankSystem\libs\_3363d0a4024b663e\CortexPE\Commando\BaseCommand;
use IvanCraft623\RankSystem\libs\_3363d0a4024b663e\CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\utils\Utils;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use function is_string;
use function str_replace;
use function time;

final class UserInfoCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("userinfo", "Shows info about a user");
		$this->setPermission("ranksystem.command.userinfo");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new RawStringArgument("user"));
	}

	/**
	 * @param mixed[] $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$user = $args["user"];
		if (!is_string($user)) {
			throw new AssumptionFailedError("Expected string argument \"user\"");
		}

		$session = $this->plugin->getSessionManager()->get($user);
		if ($sender instanceof Player) {
			$this->plugin->getFormManager()->sendUserInfo($sender, $session, $sender->hasPermission("ranksystem.command.manage"));
		} else {
			$session->onInitialize(function () use ($sender, $session) {
				$translator = $this->plugin->getTranslator();
				$permissions = "";
				foreach ($session->getUserPermissions() as $permission) {
					$time = $session->getPermissionExpTime($permission);
					if ($time !== null) {
						$time = $time - time();
						if ($time < 0) {
							$time = null;
						}
					}
					$permissions .= "\n §e - " . $permission . " §7(" . ($time === null ? $translator->translate($sender, "text.never") : Utils::getTimeTranslated($time, $translator, $sender)) . ")";
				}
				$ranks = "";
				foreach ($session->getRanks() as $rank) {
					$time = $session->getRankExpTime($rank);
					if ($time !== null) {
						$time = $time - time();
						if ($time < 0) {
							$time = null;
						}
					}
					$ranks .= "\n §e - " . $rank->getName() . " §7(" . ($time === null ? $translator->translate($sender, "text.never") : Utils::getTimeTranslated($time, $translator, $sender)) . ")";
				}
				$sender->sendMessage(
					"§r§f" . $translator->translate($sender, "text.user") . ": §a" . $session->getName() . "\n\n" .
					"§r§f" . $translator->translate($sender, "text.nametag") . ": " . $session->getNameTagFormat() . "\n" .
					"§r§f" . $translator->translate($sender, "text.chat") . ": " . str_replace("{message}", $translator->translate($sender, "text.hello_world"), $session->getChatFormat()) . "\n\n" .
					"§r§f" . $translator->translate($sender, "text.ranks") . ": " . $ranks . "\n" .
					"§r§f" . $translator->translate($sender, "text.permissions") . ": §a" . $permissions
				);
			});
		}
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}