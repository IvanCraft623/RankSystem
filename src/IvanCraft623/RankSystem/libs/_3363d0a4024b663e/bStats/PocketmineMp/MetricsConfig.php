<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_3363d0a4024b663e\bStats\PocketmineMp;

use function file;
use function file_exists;
use function file_put_contents;
use function implode;
use function is_dir;
use function mkdir;
use function random_int;
use function sprintf;
use function str_starts_with;
use function substr;
use function strlen;

class MetricsConfig
{
    private string $filePath;
    private bool $defaultEnabled;

    private string $serverUUID;
    private bool $enabled;
    private bool $logErrors;
    private bool $logSentData;
    private bool $logResponseStatusText;

    private bool $didExistBefore = true;

    /**
     * @throws \RuntimeException if the config file cannot be read or written
     */
    public function __construct(string $filePath, bool $defaultEnabled)
    {
        $this->filePath = $filePath;
        $this->defaultEnabled = $defaultEnabled;

        $this->setupConfig();
    }

    public function getServerUUID(): string
    {
        return $this->serverUUID;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isLogErrorsEnabled(): bool
    {
        return $this->logErrors;
    }

    public function isLogSentDataEnabled(): bool
    {
        return $this->logSentData;
    }

    public function isLogResponseStatusTextEnabled(): bool
    {
        return $this->logResponseStatusText;
    }

    public function didExistBefore(): bool
    {
        return $this->didExistBefore;
    }

    private function setupConfig(): void
    {
        if (!file_exists($this->filePath)) {
            $this->didExistBefore = false;
            $this->writeConfig();
        }
        $this->readConfig();
        if (!isset($this->serverUUID)) {
            $this->writeConfig();
            $this->readConfig();
        }
    }

    private function writeConfig(): void
    {
        $dir = \dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $lines = [
            "# bStats (https://bStats.org) collects some basic information for plugin authors, like",
            "# how many people use their plugin and their total player count. It's recommended to keep",
            "# bStats enabled, but if you're not comfortable with this, you can turn this setting off.",
            "# There is no performance penalty associated with having metrics enabled, and data sent to",
            "# bStats is fully anonymous.",
            "# Learn more here: https://bstats.org/docs/server-owners",
            "enabled=" . ($this->defaultEnabled ? "true" : "false"),
            "server-uuid=" . self::generateUuidV4(),
            "log-errors=false",
            "log-sent-data=false",
            "log-response-status-text=false",
        ];
        file_put_contents($this->filePath, implode("\n", $lines) . "\n");
    }

    private function readConfig(): void
    {
        $lines = file($this->filePath, \FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new \RuntimeException("Content of newly created file is null");
        }

        $this->enabled = ($this->getConfigValue("enabled", $lines) ?? "true") === "true";
        $this->serverUUID = $this->getConfigValue("server-uuid", $lines) ?? "";
        if ($this->serverUUID === "") {
            unset($this->serverUUID);
            return;
        }
        $this->logErrors = ($this->getConfigValue("log-errors", $lines) ?? "false") === "true";
        $this->logSentData = ($this->getConfigValue("log-sent-data", $lines) ?? "false") === "true";
        $this->logResponseStatusText = ($this->getConfigValue("log-response-status-text", $lines) ?? "false") === "true";
    }

    /**
     * @param list<string> $lines
     */
    private function getConfigValue(string $key, array $lines): ?string
    {
        $prefix = $key . "=";
        foreach ($lines as $line) {
            if (str_starts_with($line, $prefix)) {
                return substr($line, strlen($prefix));
            }
        }
        return null;
    }

    private static function generateUuidV4(): string
    {
        $data = [];
        for ($i = 0; $i < 16; $i++) {
            $data[] = random_int(0, 255);
        }
        $data[6] = ($data[6] & 0x0f) | 0x40; // version 4
        $data[8] = ($data[8] & 0x3f) | 0x80; // variant 1

        return sprintf(
            "%02x%02x%02x%02x-%02x%02x-%02x%02x-%02x%02x-%02x%02x%02x%02x%02x%02x",
            $data[0],
            $data[1],
            $data[2],
            $data[3],
            $data[4],
            $data[5],
            $data[6],
            $data[7],
            $data[8],
            $data[9],
            $data[10],
            $data[11],
            $data[12],
            $data[13],
            $data[14],
            $data[15]
        );
    }
}