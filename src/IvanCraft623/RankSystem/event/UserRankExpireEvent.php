<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\event;

use IvanCraft623\RankSystem\{RankSystem as Ranks, session\Session, rank\Rank};

use pocketmine\event\Event;

class UserRankExpireEvent extends Event {

	/** @var null $handlerList */
	public static $handlerList = \null;

	/** @var Session $session */
	protected $session;

	/** @var Rank $rank */
	protected $rank;

	/**
	 * UserRankExpireEvent constructor.
	 * @param Session $session
	 * @param Rank $rank
	 */
	public function __construct(Session $session, Rank $rank) {
		$this->session = $session;
		$this->rank = $rank;
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
}