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

namespace IvanCraft623\RankSystem\utils;

use Ifera\ScoreHud\event\PlayerTagsUpdateEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;

use InvalidArgumentException;

use IvanCraft623\RankSystem\libs\_226f1fc83fe584a7\IvanCraft623\languages\Translator;
use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\session\Session;

use pocketmine\command\CommandSender;

use function ceil;
use function class_exists;
use function floor;
use function implode;
use function preg_match;
use function strlen;
use function strtotime;
use function substr;
use function time;
use function trim;

/**
 * @phpstan-type Time array{
 * 	years: int,
 * 	months: int,
 * 	days: int,
 * 	hours: int,
 * 	minutes: int,
 * 	seconds: int
 * }
 */
final class Utils {

	public static bool $scoreHudDetected;

	/**
	 * @return Time
	 */
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

	public static function getTimeTranslated(int $seconds, ?Translator $translator = null, ?CommandSender $sender = null) : string {
		$time = [];
		foreach (self::getTime($seconds) as $key => $value) {
			if ($value !== 0 || $key === "seconds") {
				if ($translator !== null) {
					$time[] = $value . " " . $translator->translate($sender, "text.time." . $key);
				} else {
					$time[] = $value . " " . $key;
				}
			}
		}
		return implode(", ", $time);
	}

	/**
	 * @param string $duration Must be of the form [ay][bM][cw][dd][eh][fm] with a, b, c, d, e, f integers
	 * @return ?Int UNIX timestamp corresponding to the duration (1y will return the timestamp one year from now)
	 * Credits for adeynes
	 */
	public static function parseDuration(string $duration, ?Translator $translator = null, ?CommandSender $sender = null) : ?int {
		$time_units = ['y' => 'year', 'M' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'm' => 'minute'];
		if ($translator !== null) {
			$new_units = [];
			foreach ($time_units as $key => $unit) {
				$new_units[$translator->translate($sender, "time.argument." . $unit)] = $unit;
			}
			$time_units = $new_units;
		}
		$regex = "/^";
		foreach ($time_units as $key => $unit) {
			$regex .= "([0-9]+" . $key . ")?";
		}
		$regex .= "$/";
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
		if ($time === "") {
			return time();
		}

		$result = strtotime($time);
		if ($result === false) {
			$result = null;
		}
		return $result;
	}

	/**
	 * @param Rank[] $ranks
	 *
	 * @return string[]
	 */
	public static function getRanksNames(array $ranks) : array {
		$ranksNames = [];
		foreach ($ranks as $rank) {
			$ranksNames[] = $rank->getName();
		}
		return $ranksNames;
	}

	/**
	 * @param Rank[] $ranks
	 */
	public static function ranks2string(array $ranks) : string {
		return implode(", ", self::getRanksNames($ranks));
	}

	public static function updateScoreTags(Session $session) : void {
		if (!isset(self::$scoreHudDetected)) {
			self::$scoreHudDetected = class_exists(PlayerTagsUpdateEvent::class);
		}
		if (self::$scoreHudDetected) {
			$player = $session->getPlayer();
			if ($player !== null) {
				(new PlayerTagsUpdateEvent($player, [ // @phpstan-ignore-line
					new ScoreTag("ranksystem.ranks", self::ranks2string($session->getRanks())), // @phpstan-ignore-line
					new ScoreTag("ranksystem.highest_rank", $session->getHighestRank()->getName()), // @phpstan-ignore-line
					new ScoreTag("ranksystem.nametag", $session->getNameTagFormat()) // @phpstan-ignore-line
				]))->call();
			}
		}
	}
}