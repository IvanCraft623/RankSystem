<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_d3d86656f72b4055\IvanCraft623\languages;

use InvalidArgumentException;

class Language {

	private string $locale;

	private string $name;

	private array $translations;
	
	public function __construct(string $locale, array $translations) {
		$this->locale = $locale;
		if (!isset($translations["name"])) {
			throw new InvalidArgumentException("Translation \"name\" not found in $locale");
		}
		$this->name = $translations["name"];
		$this->translations = $translations;
	}

	public function getLocale(): string {
		return $this->locale;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getTranslations(): array {
		return $this->translations;
	}

	public function getTranslation(string $key): ?string {
		return $this->translations[$key] ?? null;
	}
}