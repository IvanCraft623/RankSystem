<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\event;

use IvanCraft623\RankSystem\rank\Rank;
use IvanCraft623\RankSystem\session\Session;

use pocketmine\event\Event;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

class UserRankSetEvent extends Event implements Cancellable {
	use CancellableTrait;

	protected Session $session;

	protected Rank $rank;

	protected ?int $expTime;

	/**
	 * PlayerRankSetEvent constructor.
	 * @param Session $session
	 * @param Rank $rank
	 * @param ?int $expTime
	 */
	public function __construct(Session $session, Rank $rank, ?int $expTime) {
		$this->session = $session;
		$this->rank = $rank;
		$this->expTime = $expTime;
	}

	/**
	 * @return Session
	 */
	public function getSession() : Session {
		return $this->session;
	}

	/**
	 * @return Rank
	 */
	public function getRank() : Rank {
		return $this->rank;
	}

	/**
	 * @return ?Int
	 */
	public function getExpTime() : ?int {
		return $this->expTime;
	}
}