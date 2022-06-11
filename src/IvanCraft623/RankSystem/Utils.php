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

final class Utils {

	public static function getTimeLeft(int|string $expTime) : ?array {
		if (time() < $expTime) {
			$leftTime = $expTime - time();
			$day = floor($leftTime / 86400);
			$hourSeconds = $leftTime % 86400;
			$hour = floor($hourSeconds / 3600);
			$minuteSec = $hourSeconds % 3600;
			$minute = floor($minuteSec / 60);
			$remainingSec = $minuteSec % 60;
			$second = ceil($remainingSec);
			$remainingTime = [
				"Days" => $day,
				"Hours" => $hour,
				"Minutes" => $minute,
				"Seconds" => $second
			];
			return $remainingTime;
		}
		return null;
	}

	public static function getExpIn(int|string $expTime) : ?string {
		if (is_numeric($expTime) && $expTime > 0) {
			$time = self::getTimeLeft($expTime);
			return $time["Days"]." day(s), ".$time["Hours"]." hour(s), ".$time["Minutes"]." minute(s), ".$time["Seconds"]." second(s)";
		}
		return "Never";
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
}