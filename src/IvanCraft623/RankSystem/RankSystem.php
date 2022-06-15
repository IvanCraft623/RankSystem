<?php

#Plugin By:

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

namespace IvanCraft623\RankSystem;

use IvanCraft623\RankSystem\command\RanksCommand;
use IvanCraft623\RankSystem\session\SessionManager;
use IvanCraft623\RankSystem\rank\RankManager;
use IvanCraft623\RankSystem\task\UpdateTask;
use IvanCraft623\RankSystem\migrator\LegacyRankSystem;
use IvanCraft623\RankSystem\migrator\Migrator;
use IvanCraft623\RankSystem\migrator\MigratorManager;
use IvanCraft623\RankSystem\migrator\PurePerms;
use IvanCraft623\RankSystem\provider\Provider;
use IvanCraft623\RankSystem\provider\libasynql as libasynqlProvider;

use pocketmine\permission\PermissionManager;
use pocketmine\permission\DefaultPermissions;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class RankSystem extends PluginBase {
	use SingletonTrait;

	private static array $globalPerms = [];

	private static array $pmDefaultPerms = [];

	private Provider $provider;

	public function onLoad() : void {
		self::setInstance($this);
		self::$globalPerms = $this->getConfig()->get("Global_Perms");
		$this->saveResources();
		$this->getRankManager()->load();
	}

	public function onEnable() : void {
		$this->loadCommands();
		$this->loadListeners();
		$this->loadProvider();
		$this->loadMigrators();
		$this->getScheduler()->scheduleRepeatingTask(new UpdateTask(), 60);
	}

	public function getProvider() : Provider{
		return $this->provider;
	}

	public function getSessionManager() : SessionManager {
		return SessionManager::getInstance();
	}

	public function getRankManager() : RankManager {
		return RankManager::getInstance();
	}

	public function getMigratorManager() : MigratorManager {
		return MigratorManager::getInstance();
	}

	public function getConfigs(string $value) : Config {
		return new Config(self::getInstance()->getDataFolder() . $value, Config::YAML);
	}

	public function getGlobalPerms() : array {
		return self::$globalPerms;
	}

	/**
	 * From PurePerms
	 */
	public function getPmmpPerms() : array {
		if (self::$pmDefaultPerms === []) {
			foreach (PermissionManager::getInstance()->getPermissions() as $permission) {
				if (strpos($permission->getName(), "pocketmine") !== false) {
					self::$pmDefaultPerms[] = $permission;
				}
			}
		}
		return self::$pmDefaultPerms;
	}

	public function getPluginPerms(PluginBase $plugin) : array {
		$pluginPerms = [];
		foreach ($plugin->getDescription()->getPermissions() as $default => $perms) {
			foreach ($perms as $perm) {
				$pluginPerms[] = $perm;
			}
		}
		return $pluginPerms;
	}

	public function saveResources() : void {
		$this->saveResource("config.yml");
		$this->saveResource("ranks.yml");
	}

	private function loadCommands() : void {
		$values = [new RanksCommand($this)];
		foreach ($values as $commands) {
			$this->getServer()->getCommandMap()->register('RankSystem', $commands);
		}
		unset($values);
	}

	private function loadListeners() : void {
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
	}

	private function loadProvider() : void {
		if (!isset($this->provider)) {
			$name = $this->getConfig()->get("database")["type"] ?? "";
			switch (strtolower($name)) {
				case "sqlite":
				case "sqlite3":
				case "sq3":
				case "mysql":
				case "mysqli":
					$provider = libasynqlProvider::class;
					break;

				default:
					$this->getLogger()->critical("Unknown database type: " . $name);
					throw new DisablePluginException("Unknown database type: " . $name); // @phpstan-ignore-line
			}
			$this->setProvider($provider::getInstance());
		}
	}

	public function loadMigrators() : void {
		$migrator = $this->getMigratorManager();
		$migrator->register(new LegacyRankSystem());
		$migrator->register(new PurePerms());

		foreach ($migrator->getAll() as $migrator) {
			if ($migrator->canMigrate() && !$migrator->hasMigrated()) {
				$this->getLogger()->notice("Migrating data from: " . $migrator->getName());
				if ($migrator->migrate()) {
					$this->getLogger()->info("§a" . $migrator->getName() . " data has migrated successfully!");
				} else {
					$this->getLogger()->warning("Failed to migrate data from " . $migrator->getName());
				}
			}
		}
	}

	public function setProvider(Provider $provider) : void {
		$databaseFolder = $this->getDataFolder() . "database";
		if (!file_exists($databaseFolder)) {
			mkdir($databaseFolder, 0777);
		}
		$provider->load();
		$this->provider = $provider;
		$this->getLogger()->info("User provider was set to: " . $provider->getName());
	}
}
