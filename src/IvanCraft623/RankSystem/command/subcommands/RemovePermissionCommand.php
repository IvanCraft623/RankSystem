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

use IvanCraft623\RankSystem\libs\_2f17bee6c3ea9171\CortexPE\Commando\args\RawStringArgument;
use IvanCraft623\RankSystem\libs\_2f17bee6c3ea9171\CortexPE\Commando\BaseCommand;
use IvanCraft623\RankSystem\libs\_2f17bee6c3ea9171\CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\RankSystem;

use pocketmine\command\CommandSender;
use pocketmine\utils\AssumptionFailedError;
use function is_string;

final class RemovePermissionCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("removepermission", "Remove a permission from a user", ["removeperm"]);
		$this->setPermission("ranksystem.command.removepermission");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new RawStringArgument("user"));
		$this->registerArgument(1, new RawStringArgument("permission"));
	}

	/**
	 * @param mixed[] $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$user = $args["user"];
		if (!is_string($user)) {
			throw new AssumptionFailedError("Expected string argument \"user\"");
		}

		$permission = $args["permission"];
		if (!is_string($permission)) {
			throw new AssumptionFailedError("Expected string argument \"permission\"");
		}

		$session = $this->plugin->getSessionManager()->get($user);
		$session->onInitialize(function () use ($session, $sender, $user, $permission) {
			$translator = $this->plugin->getTranslator();
			if (!$session->hasUserPermission($permission)) {
				$sender->sendMessage($translator->translate($sender, "user.remove_permission.no_permission", [
					"{%user}" => $user,
					"{%permission}" => $permission
				]));
			} else {
				$session->removePermission($permission);
				$sender->sendMessage($translator->translate($sender, "user.remove_permission.success", [
					"{%user}" => $user,
					"{%permission}" => $permission
				]));
			}
		});
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}