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

namespace IvanCraft623\RankSystem\task;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;
use pocketmine\utils\Utils;

class SponsorsListTask extends AsyncTask {

	private const TLS_KEY_COMPLETION_CALLBACK = "completionCallback";

	private string $sponsorsURL;

	/**
	 * @param \Closure(string[]|null $members) : void $onCompletion
	 */
	public function __construct(string $url, \Closure $onCompletion) {
		$this->storeLocal(self::TLS_KEY_COMPLETION_CALLBACK, $onCompletion);
		$this->sponsorsURL = $url;
	}

	public function onRun(): void {
		$body = Internet::getURL($this->sponsorsURL)?->getBody();
		if ($body === null) {
			$this->setResult(null);
			return;
		}

		$data = json_decode($body, true);
		if (!is_array($data)) {
			$this->setResult(null);
			return;
		}

		try {
			Utils::validateArrayValueType($data, static function(string $_) : void{});
			$this->setResult($data);
		} catch(\TypeError $e) {
			$this->setResult(null);
		}
	}

	public function onCompletion(): void {
		/** @var \Closure|null $callback */
		$callback = $this->fetchLocal(self::TLS_KEY_COMPLETION_CALLBACK);
		if ($callback !== null) {
			$callback($this->getResult());
		}
	}
}
