<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_a2d25fd8ce5d9237\IvanCraft623\languages;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

use InvalidArgumentException;

final class Translator {

	public static array $translators = [];

	public const MINECRAFT_LOCALES = [
		// See  ->  https://github.com/Mojang/bedrock-samples/blob/main/resource_pack/texts/language_names.json
		"en_US", // English (United States)
		"en_GB", // English (United Kingdom)
		"de_DE", // Deutsch (Deutschland)
		"es_ES", // Español (España)
		"es_MX", // Español (México)
		"fr_FR", // Français (France)
		"fr_CA", // Français (Canada)
		"it_IT", // Italiano (Italia)
		"ja_JP", // 日本語 (日本)
		"ko_KR", // 한국어 (대한민국)
		"pt_BR", // Português (Brasil)
		"pt_PT", // Português (Portugal)
		"ru_RU", // Русский (Россия)
		"zh_CN", // 中文(简体)
		"zh_TW", // 中文(繁體)
		"nl_NL", // Nederlands (Nederland)
		"bg_BG", // Български (България)
		"cs_CZ", // Čeština (Česko)
		"da_DK", // Dansk (Danmark)
		"el_GR", // Ελληνικά (Ελλάδα)
		"fi_FI", // Suomi (Suomi)
		"hu_HU", // Magyar (Magyarország)
		"id_ID", // Indonesia (Indonesia)
		"nb_NO", // Norsk bokmål (Norge)
		"pl_PL", // Polski (Polska)
		"sk_SK", // Slovenčina (Slovensko)
		"sv_SE", // Svenska (Sverige)
		"tr_TR", // Türkçe (Türkiye)
		"uk_UA", // Українська (Україна)
	];

	private PluginBase $plugin;

	/** @var Language[] */
	private array $languages = [];

	private Language $defaultLanguage;
	
	public function __construct(PluginBase $plugin) {
		$this->plugin = $plugin;
	}

	public function getPlugin(): PluginBase{
		return $this->plugin;
	}

	public function getDefaultLanguage(): Language {
		return $this->defaultLanguage;
	}

	public function setDefaultLanguage(Language $language): void {
		if (!in_array($language, $this->languages, true)) {
			throw new InvalidArgumentException("Language is not registered");
		}
		$this->defaultLanguage = $language;
	}

	public function isLanguageRegistered(string $locale): bool {
		return isset($this->languages[$locale]);
	}

	public function registerLanguage(Language $language, bool $override = false): void {
		$locale = $language->getLocale();
		if (!in_array($language->getLocale(), self::MINECRAFT_LOCALES, true)) {
			throw new InvalidArgumentException("Language ".$locale." is not available in minecraft");
		}
		if (!$override && $this->isLanguageRegistered($locale)) {
			throw new InvalidArgumentException("Trying to overwrite an already registered Language");
		}
		$this->languages[$locale] = $language;
	}

	public function getLanguages(): array {
		return $this->languages;
	}

	public function getLanguage(string $locale): ?Language {
		return $this->languages[$locale] ?? null;
	}

	public function translate(?CommandSender $target, string $key, array $replacements = []): string {
		$language = (($target instanceof Player) ? ($this->languages[$target->getLocale()] ?? null) : null) ?? $this->defaultLanguage;
		$translation = $language->getTranslation($key);

		if ($translation === null) {
			$defaultTranslation = $this->defaultLanguage->getTranslation($key);
			if ($defaultTranslation === null) {
				$this->plugin->getLogger()->error("Unknown translation key ".$key);
				return "";
			} else {
				$translation = $defaultTranslation;
			}
		}
		return str_replace(array_keys($replacements), array_values($replacements), $translation);
	}
}