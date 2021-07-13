<?php

#Ranks by IvanCraft623 (Twitter: @IvanCraft623)

/*
    8888888                            .d8888b.                   .d888 888     .d8888b.   .d8888b.   .d8888b.  
      888                             d88P  Y88b                 d88P"  888    d88P  Y88b d88P  Y88b d88P  Y88b 
      888                             888    888                 888    888    888               888      .d88P 
      888  888  888  8888b.  88888b.  888        888d888 8888b.  888888 888888 888d888b.       .d88P     8888"  
      888  888  888     "88b 888 "88b 888        888P"      "88b 888    888    888P "Y88b  .od888P"       "Y8b. 
      888  Y88  88P .d888888 888  888 888    888 888    .d888888 888    888    888    888 d88P"      888    888 
      888   Y8bd8P  888  888 888  888 Y88b  d88P 888    888  888 888    Y88b.  Y88b  d88P 888"       Y88b  d88P 
    8888888  Y88P   "Y888888 888  888  "Y8888P"  888    "Y888888 888     "Y888  "Y8888P"  888888888   "Y8888P"  
*/

declare(strict_types=1);

namespace IvanCraft623\RankSystem\command;

use IvanCraft623\RankSystem\{RankSystem as Ranks, Utils};

use pocketmine\{Server, Player, plugin\PluginBase};
use pocketmine\command\{PluginCommand, CommandSender};

class RanksCommand extends PluginCommand {

	/** @var Ranks */
	private $plugin;

	/**
	 * RanksCommand Constructor
	 * @param Ranks $plugin
	 */
	public function __construct(Ranks $plugin) {
		parent::__construct('ranks', $plugin);
		$this->plugin = $plugin;
		$this->setDescription('A Ranks/Perms manager by IvanCraft623.');
	}

	public function execute(CommandSender $sender, string $label, array $args) {
		if (isset($args[0])) {
			switch ($args[0]) {
				case 'create': //TODO
					if (!$sender instanceof Player) {
						$sender->sendMessage(
							"§cYou can only use this command in game!"."\n".
							"§eTo create a rank you must go to §bplugin_data/Ranks/ranks.yml §eand add it manually."
						);
						return true;
					}
					if (!$sender->hasPermission("ranksystem.commands")) {
						$sender->sendMessage("§cYou do not have permission to use this command!");
						return true;
					}
					if (!isset($args[1])) {
						$sender->sendMessage("§cUse: /ranks create <rank>");
						return true;
					}
					if ($this->plugin->getRankManager()->exists($args[1])) {
						$sender->sendMessage("§c".$args[1]." rank already exist!");
						return true;
					}
					$sender->sendMessage("§cThis function is under development!");
				break;

				case 'delete':
					if (!$sender->hasPermission("ranksystem.commands")) {
						$sender->sendMessage("§cYou do not have permission to use this command!");
						return true;
					}
					if (!isset($args[1])) {
						$sender->sendMessage("§cUse: /ranks delete <rank>");
						return true;
					}
					if ($this->plugin->getRankManager()->exists($args[1])) {
						$sender->sendMessage("§c".$args[1]." rank does not exist!");
						return true;
					}
					if ($args[1] === $this->plugin->getRankManager()->getDefault()->getName()) {
						$sender->sendMessage("§cYou cannot delete the default rank!");
						return true;
					}
					$this->plugin->getRankManager()->delete($args[1]);
				break;

				case 'edit': //TODO
					if (!$sender->hasPermission("ranksystem.commands")) {
						$sender->sendMessage("§cYou do not have permission to use this command!");
						return true;
					}
					if (!isset($args[1])) {
						$sender->sendMessage("§cUse: /ranks edit <rank>");
						return true;
					}
					$sender->sendMessage("§cThis function is under development!");
				break;

				case 'list':
					if (!$sender->hasPermission("ranksystem.commands")) {
						$sender->sendMessage("§cYou do not have permission to use this command!");
						return true;
					}
					$ranks = $this->plugin->getRankManager()->getAll();
					$sender->sendMessage("§aRanks (".count($ranks)."):");
					foreach ($ranks as $rank) {
						$sender->sendMessage("§f» §e".$rank->getName());
					}
				break;

				case 'set':
				case 'setrank':
					if (!$sender->hasPermission("ranksystem.commands")) {
						$sender->sendMessage("§cYou do not have permission to use this command!");
						return true;
					}
					if (count($args) == 2) {
						#if ($sender instanceof Player) {
							# send to form, but forms are not ready D:
							#return true;
						#}
					}
					if (!isset($args[2])) {
						$sender->sendMessage(
							"§cUse: /ranks set <player> <rank> [timeToExpire]"."\n".
							"§bIf §a[timToExpire]§b is not specified the rank will not expire"."\n"."\n".
							"§aIf you want the rank to expire, §e[timeToExpire]§a must specify 4 data as follows:"."\n".
							"§edays, hours and minutes. §bExample: §e1d2h3m"."\n".
							"§bIn this case the range will expire in 1 day, 2 hours and 3 minutes."
						);
						return true;
					}
					if (!$this->plugin->getRankManager()->exists($args[2])) {
						$sender->sendMessage("§c".$args[2]." rank does not exist!");
						return true;
					}
					$session = $this->plugin->getSessionManager()->get($args[1]);
					if ($session->hasRank($args[2])) {
						$sender->sendMessage("§c".$args[1]." already has the rank ".$args[2]."!");
						return true;
					}
					$rank = $this->plugin->getRankManager()->getByName($args[2]);
					if (!isset($args[3])) {
						$expTime = "Never";
						$session->setRank($rank);
					} elseif (($expTime = Utils::parseDuration($args[3])) !== null) {
						$session->setRank($rank, $expTime);
					} else {
						$sender->sendMessage(
							"§cInvalid timeToExpire provided!"."\n".
							"§bIf §a[timToExpire]§b is not specified the rank will not expire"."\n"."\n".
							"§aIf you want the rank to expire, §e[timeToExpire]§a must specify 4 data as follows:"."\n".
							"§edays, hours and minutes. §bExample: §e1d2h3m"."\n".
							"§bIn this case the range will expire in 1 day, 2 hours and 3 minutes."
						);
						return true;
					}
					$sender->sendMessage(
						"§a---- §6You have given Rank! §a----"."\n"."\n".
						"§ePlayer:§b {$args[1]}"."\n".
						"§eRank:§b {$args[2]}"."\n".
						"§eExpire In:§b ".Utils::getExpIn($expTime)
					);
				break;

				case 'remove':
				case 'removerank':
					if (!$sender->hasPermission("ranksystem.commands")) {
						$sender->sendMessage("§cYou do not have permission to use this command!");
						return true;
					}
					if (!isset($args[2])) {
						$sender->sendMessage("§cUse: /ranks remove <player> <rank>");
						return true;
					}
					if (!$this->plugin->getRankManager()->exists($args[2])) {
						$sender->sendMessage("§c".$args[2]." rank does not exist!");
						return true;
					}
					$session = $this->plugin->getSessionManager()->get($args[1]);
					if (!$session->hasRank($args[2])) {
						$sender->sendMessage("§c".$args[1]." does not have the rank ".$args[2]."!");
						return true;
					}
					$session->removeRank($this->plugin->getRankManager()->getByName($args[2]));
					$sender->sendMessage("§bYou have successfully §cremoved§b the rank §e".$args[2]." §bof §a".$args[1]);
				break;

				case 'setperm':
					if (!$sender->hasPermission("ranksystem.commands")) {
						$sender->sendMessage("§cYou do not have permission to use this command!");
						return true;
					}
					if (!isset($args[2])) {
						$sender->sendMessage("§cUse: /ranks setperm <player> <permission>");
						return true;
					}
					$session = $this->plugin->getSessionManager()->get($args[1]);
					if ($session->hasPermission($args[2])) {
						$sender->sendMessage("§c".$args[1]." already have the permission ".$args[2]);
						return true;
					}
					$session->setPermission($args[2]);
					$sender->sendMessage("§bYou have successfully §agive§b the permission §e".$args[2]." §b to ".$args[1]);
				break;

				case 'removeperm':
					if (!$sender->hasPermission("ranksystem.commands")) {
						$sender->sendMessage("§cYou do not have permission to use this command!");
						return true;
					}
					if (!isset($args[2])) {
						$sender->sendMessage("§cUse: /ranks removeperm <player> <permission>");
						return true;
					}
					$session = $this->plugin->getSessionManager()->get($args[1]);
					if (!$session->hasPermission($args[2])) {
						$sender->sendMessage("§c".$args[1]." does not have the permission ".$args[2]);
						return true;
					}
					$session->removePermission($args[2]);
					$sender->sendMessage("§bYou have successfully §cremove§b the permission §e".$args[2]." §b to ".$args[1]);
				break;

				case 'perms': // Code from PurePerms :D
					if (!isset($args[1])) {
						$sender->sendMessage("§cUse: /ranks perms <plugin>");
						return true;
					}
					$plugin = (strtolower($args[1]) === 'pocketmine' || strtolower($args[1]) === 'pmmp') ? 'pocketmine' : Server::getInstance()->getPluginManager()->getPlugin($args[1]);
					if ($plugin === null) {
						$sender->sendMessage("§cPlugin ".$args[1]." does NOT exist!");
						return true;
					}
					$permissions = ($plugin instanceof PluginBase) ? Ranks::getInstance()->getPluginPerms($plugin) : Ranks::getInstance()->getPmmpPerms();
					if (empty($permissions)) {
						$sender->sendMessage("§e".$args[1]." doesn't have any permissions!");
						return true;
					}
					$pageHeight = $sender instanceof Player ? 6 : 48;
					$chunkedPermissions = array_chunk($permissions, $pageHeight);
					$maxPageNumber = count($chunkedPermissions);
					if (!isset($args[2]) || !is_numeric($args[2]) || $args[2] <= 0) {
						$pageNumber = 1;
					} elseif ($args[2] > $maxPageNumber) {
						$pageNumber = $maxPageNumber;
					} else {
						$pageNumber = $args[2];
					}
					$sender->sendMessage("§bList of all plugin permissions from §e".$args[1]." §f(§2".$pageNumber." §7/ §2".$maxPageNumber."§f)§b :");
					foreach ($chunkedPermissions[$pageNumber - 1] as $permission) {
						$sender->sendMessage(" §f- §a".$permission->getName());
					}
				break;

				case 'credits':
					$sender->sendMessage(
						"§a---- §6Ranks §bCredits §a----"."\n"."\n".
						"§eAuthor: §7IvanCraft623 / IvanCraft236"."\n".
						"§eStatus: §7Public"."\n"."\n".
						"§bThis is a Rank / Permission Management plugin"
					);
				break;
				
				default:
					self::sendUsageMessage($sender);
				break;
			}
		} else {
			self::sendUsageMessage($sender);
		}
		return true;
	}

	public static function sendUsageMessage(CommandSender $sender) : void {
		if ($sender->hasPermission("ranksystem.commands")) {
			$sender->sendMessage(
				"§a---- §5Ranks §bCommands §a----"."\n"."\n".
				"§eUse:§a /ranks create §7(Create a Rank.)"."\n".
				"§eUse:§a /ranks delete §7(Delete a Rank.)"."\n".
				"§eUse:§a /ranks edit §7(Edit Rank data.)"."\n".
				"§eUse:§a /ranks list §7(Show the Ranks List.)"."\n".
				"§eUse:§a /ranks set §7(Set a Rank to a Player.)"."\n".
				"§eUse:§a /ranks remove §7(Remove a Rank from a Player.)"."\n".
				"§eUse:§a /ranks setperm §7(Set to a player a specific permission.)"."\n".
				"§eUse:§a /ranks removeperm §7(Remove to a player a specific permission.)"."\n".
				"§eUse:§a /ranks perms §7(Show Permissions of a plugin.)"."\n"."\n".
				"§eUse:§a /ranks credits §7(Show Ranks credits.)"
			);
		} else {
			$sender->sendMessage(
				"§a---- §5Ranks §bCommands §a----"."\n"."\n".
				"§eUse:§a /ranks credits §7(Show Ranks credits.)"
			);
		}
	}
}
