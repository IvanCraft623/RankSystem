<?php

namespace IvanCraft623\RankSystem\utils;

use IvanCraft623\RankSystem\RankSystem;
use IvanCraft623\RankSystem\session\SessionManager;
use MohamadRZ4\Placeholder\expansion\PlaceholderExpansion;
use pocketmine\player\Player;

class PlaceholderUtils extends PlaceholderExpansion
{

    private RankSystem $rankSystem;

    public function __construct(RankSystem $rankSystem)
    {
        $this->rankSystem = $rankSystem;
    }

    public function getIdentifier(): string
    {
        return "RankSystem";
    }

    public function getAuthor(): string
    {
        return "IvanCraft623";
    }

    public function getVersion(): string
    {
        return "1.0.0";
    }


    public function onPlaceholderRequest(?Player $player, string $params): ?string
    {
        if ($player === null) return null;

        $ranksystem = $this->rankSystem;
        $sessionmanager = $ranksystem->getSessionManager();

        $session = $sessionmanager->get("IvanCraft236");
        if ($session === null) return null;

        switch ($params) {
            case "ranks":
                return implode(", ", $session->getRanks());
            case "highest_rank":
                return $session->getHighestRank()->getName();
            case "nametag":
                return $session->getNameTagFormat();
            default:
                return null;
        }
    }
}