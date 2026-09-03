<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_7c604e93589d947c\bStats\PocketmineMp;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use function gzencode;
use function is_string;
use function str_starts_with;

class SubmitDataTask extends AsyncTask
{
    private const REPORT_URL = "https://bStats.org/api/v2/data/pocketmine";

    private string $jsonData;
    private bool $logErrors;
    private bool $logResponseStatusText;

    public function __construct(string $jsonData, bool $logErrors, bool $logResponseStatusText)
    {
        $this->jsonData = $jsonData;
        $this->logErrors = $logErrors;
        $this->logResponseStatusText = $logResponseStatusText;
    }

    public function onRun(): void
    {
        $compressed = gzencode($this->jsonData);
        if ($compressed === false) {
            $this->setResult("error: Failed to gzip compress data");
            return;
        }

        $result = Internet::postURL(
            self::REPORT_URL,
            $compressed,
            10,
            [
                "Content-Encoding: gzip",
                "Content-Type: application/json",
                "User-Agent: Metrics-Service/1",
                "Accept: application/json",
                "Connection: close",
            ]
        );

        if ($result === null) {
            $this->setResult("error: Request failed");
            return;
        }

        $this->setResult($result->getBody());
    }

    public function onCompletion(): void
    {
        $result = $this->getResult();
        if (!is_string($result)) {
            return;
        }
        if ($this->logResponseStatusText) {
            Server::getInstance()->getLogger()->info("Sent data to bStats and received response: " . $result);
        }
        if ($this->logErrors && str_starts_with($result, "error:")) {
            Server::getInstance()->getLogger()->warning("Could not submit bStats metrics data: " . $result);
        }
    }
}