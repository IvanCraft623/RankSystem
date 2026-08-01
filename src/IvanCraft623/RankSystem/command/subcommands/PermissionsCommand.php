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

use IvanCraft623\RankSystem\libs\_723798d82b3d20a4\CortexPE\Commando\args\IntegerArgument;
use IvanCraft623\RankSystem\libs\_723798d82b3d20a4\CortexPE\Commando\args\RawStringArgument;
use IvanCraft623\RankSystem\libs\_723798d82b3d20a4\CortexPE\Commando\BaseCommand;
use IvanCraft623\RankSystem\libs\_723798d82b3d20a4\CortexPE\Commando\BaseSubCommand;

use IvanCraft623\RankSystem\RankSystem;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use function array_chunk;
use function count;
use function is_int;
use function is_string;
use function strtolower;

final class PermissionsCommand extends BaseSubCommand {

	public function __construct(private RankSystem $plugin) {
		parent::__construct("permissions", "See permissions of plugins or pocketmine", ["perms"]);
		$this->setPermission("ranksystem.command.permissions");
	}

	protected function prepare() : void {
		$this->registerArgument(0, new RawStringArgument("source"));
		$this->registerArgument(1, new IntegerArgument("page", true));
	}

	/**
	 * @param mixed[] $args
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$source = $args["source"];
		if (!is_string($source)) {
			throw new AssumptionFailedError("Expected string argument \"source\"");
		}
		$plugin = (strtolower($source) === 'pocketmine' || strtolower($source) === 'pmmp') ? 'pocketmine' : $this->plugin->getServer()->getPluginManager()->getPlugin($source);
		if ($plugin === null) {
			$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "command.permissions.plugin_not_found"));
			return;
		}
		$permissions = ($plugin instanceof PluginBase) ? $this->plugin->getPluginPerms($plugin) : $this->plugin->getPmmpPerms();
		if (count($permissions) === 0) {
			$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "command.permissions.no_permissions"));
			return;
		}
		$pageHeight = $sender instanceof Player ? 6 : 48;
		$chunkedPermissions = array_chunk($permissions, $pageHeight);
		$maxPageNumber = count($chunkedPermissions);
		if (!isset($args["page"]) || !is_int($args["page"]) || $args["page"] <= 0) {
			$pageNumber = 1;
		} elseif ($args["page"] > $maxPageNumber) {
			$pageNumber = $maxPageNumber;
		} else {
			$pageNumber = $args["page"];
		}
			$sender->sendMessage($this->plugin->getTranslator()->translate($sender, "command.permissions.list", [
				"{%source}" => $source,
				"{%page}" => $pageNumber,
				"{%total_pages}" => $maxPageNumber
			]));
		foreach ($chunkedPermissions[$pageNumber - 1] as $permission) {
			$sender->sendMessage(" §f- §a" . $permission->getName());
		}
	}

	public function getParent() : BaseCommand {
		return $this->parent;
	}
}