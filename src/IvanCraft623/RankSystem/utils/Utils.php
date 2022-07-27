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

namespace IvanCraft623\RankSystem\utils;

use Ifera\ScoreHud\event\PlayerTagsUpdateEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use IvanCraft623\RankSystem\session\Session;
use IvanCraft623\RankSystem\rank\Rank;
use pocketmine\Server;

final class Utils {

	public static bool $scoreHudDetected;

	public static function getTime(int $seconds) : array {
		if ($seconds < 0) {
			throw new InvalidArgumentException("Seconds is lower than 0");
		}
		$year = floor($seconds / 31540000);
		$monthSec = $seconds % 31540000;
		$month = floor($monthSec / 2628000);
		$daySec = $monthSec % 2628000;
		$day = floor($daySec / 86400);
		$hourSec = $daySec % 86400;
		$hour = floor($hourSec / 3600);
		$minuteSec = $hourSec % 3600;
		$minute = floor($minuteSec / 60);
		$remainingSec = $minuteSec % 60;
		$second = ceil($remainingSec);
		return [
			"years" => (int) $year,
			"months" => (int) $month,
			"days" => (int) $day,
			"hours" => (int) $hour,
			"minutes" => (int) $minute,
			"seconds" => (int) $second
		];
	}

	public static function getTimeTranslated(int $seconds) : string {
		$time = [];
		foreach (self::getTime($seconds) as $key => $value) {
			if ($value !== 0 || $key === "seconds") {
				$time[] = $value . " " . $key;
			}
		}
		return implode(", ", $time);
	}

	/**
	 * @param string $duration Must be of the form [ay][bM][cw][dd][eh][fm] with a, b, c, d, e, f integers
	 * @return ?Int UNIX timestamp corresponding to the duration (1y will return the timestamp one year from now)
	 * Credits for adeynes
	 */
	public static function parseDuration(string $duration): ?int {
		$time_units = ['y' => 'year', 'M' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'm' => 'minute'];
		$regex = '/^([0-9]+y)?([0-9]+M)?([0-9]+w)?([0-9]+d)?([0-9]+h)?([0-9]+m)?$/';
		$matches = [];
		$is_matching = preg_match($regex, $duration, $matches);
		if (!$is_matching) {
			return null;
		}

		$time = '';

		foreach ($matches as $index => $match) {
			if ($index === 0 || strlen($match) === 0) continue; // index 0 is the full match
			$n = substr($match, 0, -1);
			$unit = $time_units[substr($match, -1)];
			$time .= "$n $unit ";
		}

		$time = trim($time);

		return $time === '' ? time() : strtotime($time);
	}

	/**
	 * @param Rank[] $ranks
	 */
	public static function ranks2string(array $ranks): string {
		$ranksNames = [];
		foreach ($ranks as $rank) {
			$ranksNames[] = $rank->getName();
		}
		return implode(", ", $ranksNames);
	}

	public static function updateScoreTags(Session $session): void {
		if (!isset(self::$scoreHudDetected)) {
			self::$scoreHudDetected = Server::getInstance()->getPluginManager()->getPlugin("ScoreHud") !== null;
		}
		if (self::$scoreHudDetected) {
			$player = $session->getPlayer();
			if ($player !== null) {
				(new PlayerTagsUpdateEvent($player, [
					new ScoreTag("ranksystem.ranks", self::ranks2string($session->getRanks())),
					new ScoreTag("ranksystem.highest_rank", $session->getHighestRank()->getName()),
					new ScoreTag("ranksystem.nametag", $session->getNameTagFormat())
				]))->call();
			}
		}
	}
}