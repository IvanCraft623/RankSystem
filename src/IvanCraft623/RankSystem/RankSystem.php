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

use CortexPE\Commando\PacketHooker;

use IvanCraft623\languages\Language;
use IvanCraft623\languages\Translator;

use IvanCraft623\RankSystem\command\RankSystemCommand;
use IvanCraft623\RankSystem\form\FormManager;
use IvanCraft623\RankSystem\rank\RankManager;
use IvanCraft623\RankSystem\session\SessionManager;
use IvanCraft623\RankSystem\tag\TagManager;
use IvanCraft623\RankSystem\task\UpdateTask;
use IvanCraft623\RankSystem\migrator\LegacyRankSystem;
use IvanCraft623\RankSystem\migrator\Migrator;
use IvanCraft623\RankSystem\migrator\MigratorManager;
use IvanCraft623\RankSystem\migrator\PurePerms;
use IvanCraft623\RankSystem\provider\Provider;
use IvanCraft623\RankSystem\provider\libasynql as libasynqlProvider;

use JackMD\ConfigUpdater\ConfigUpdater;
use JackMD\UpdateNotifier\UpdateNotifier;

use pocketmine\permission\PermissionManager;
use pocketmine\permission\DefaultPermissions;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class RankSystem extends PluginBase {
	use SingletonTrait;

	public const CONFIG_VERSION = 2;

	public const DEFAULT_LANGUAGE = "en_US";

	private static array $globalPerms = [];

	private static array $pmDefaultPerms = [];

	private Provider $provider;

	private Translator $translator;

	public function onLoad() : void {
		self::setInstance($this);

		UpdateNotifier::checkUpdate($this->getDescription()->getName(), $this->getDescription()->getVersion());
		if (ConfigUpdater::checkUpdate($this, $this->getConfig(), "config-version", self::CONFIG_VERSION)) {
			$this->reloadConfig();
		}

		self::$globalPerms = $this->getConfig()->get("Global_Perms");
		$this->saveResources();
		$this->loadTranslations();
		$this->getRankManager()->load();
		$this->getTagManager()->registerDefaults();
	}

	public function onEnable() : void {
		if (!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}

		$this->loadCommands();
		$this->loadListeners();
		$this->loadProvider();
		$this->loadMigrators();
		$this->getScheduler()->scheduleRepeatingTask(new UpdateTask(), 60);
	}

	public function getProvider() : Provider {
		return $this->provider;
	}

	public function getTranslator() : Translator {
		return $this->translator;
	}

	public function getSessionManager() : SessionManager {
		return SessionManager::getInstance();
	}

	public function getTagManager() : TagManager {
		return TagManager::getInstance();
	}

	public function getFormManager() : FormManager {
		return FormManager::getInstance();
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
		$this->saveResource("languages/en_US.ini", true);
		$this->saveResource("languages/es_MX.ini", true);
	}

	private function loadTranslations() : void {
		$this->translator = new Translator($this);
		foreach (glob($this->getDataFolder() . "languages" . DIRECTORY_SEPARATOR . "*.ini") as $file) {
			$locale = basename($file, ".ini");
			$content = parse_ini_file($file, false, INI_SCANNER_RAW);
			if ($content === false) {
				throw new AssumptionFailedError("Missing or inaccessible required resource files");
			}
			$data = array_map('\stripcslashes', $content);
			$this->translator->registerLanguage(new Language($locale, $data));
		}
		$l = $this->getConfig()->get("default-language", self::DEFAULT_LANGUAGE);
		$lang = $this->translator->getLanguage($l) ?? throw new \InvalidArgumentException("Language $l not found");
		$this->translator->setDefaultLanguage($lang);
	}

	private function loadCommands() : void {
		$values = [new RankSystemCommand($this)];
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
				$this->getLogger()->notice($this->translator->translate(null, "migrator.start", [
					"{%source}" => $migrator->getName()
				]));
				if ($migrator->migrate()) {
					$this->getLogger()->info($this->translator->translate(null, "migrator.success", [
						"{%source}" => $migrator->getName()
					]));
				} else {
					$this->getLogger()->warning($this->translator->translate(null, "migrator.fail", [
						"{%source}" => $migrator->getName()
					]));
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
		$this->getLogger()->info($this->translator->translate(null, "provider.set", [
			"{%provider}" => $provider->getName()
		]));
	}
}
