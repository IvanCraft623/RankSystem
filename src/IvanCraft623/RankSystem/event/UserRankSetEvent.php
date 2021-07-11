<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\event;

use IvanCraft623\RankSystem\{RankSystem as Ranks, rank\Rank, session\Session};

use pocketmine\event\{Event, Cancellable, CancellableTrait};

class UserRankSetEvent extends Event implements Cancellable {
	use CancellableTrait;

	/** @var null $handlerList */
	public static $handlerList = \null;

	/** @var Session $session */
	protected $session;

	/** @var Rank $rank */
	protected $rank;

	/** @var $expTime */
	protected $expTime;

	/**
	 * PlayerRankSetEvent constructor.
	 * @param Session $session
	 * @param Rank $rank
	 * @param ?Int $expTime
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