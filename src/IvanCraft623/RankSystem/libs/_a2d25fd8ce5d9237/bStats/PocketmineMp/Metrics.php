<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_a2d25fd8ce5d9237\bStats\PocketmineMp;

use IvanCraft623\RankSystem\libs\_a2d25fd8ce5d9237\bStats\PocketmineMp\charts\CustomChart;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Utils;
use function count;
use function json_encode;
use function php_uname;
use function phpversion;
use function random_int;

class Metrics
{
    public const METRICS_VERSION = "1.0.0";

    private PluginBase $plugin;
    private int $serviceId;
    private MetricsConfig $config;

    /** @var CustomChart[] */
    private array $customCharts = [];

    public function __construct(PluginBase $plugin, int $serviceId)
    {
        $this->plugin = $plugin;
        $this->serviceId = $serviceId;

        $configPath = $plugin->getServer()->getDataPath() . "bStats.txt";
        $this->config = new MetricsConfig($configPath, true);

        if (!$this->config->didExistBefore()) {
            $plugin->getLogger()->info("bStats (https://bStats.org) has started collecting data. You can opt-out by editing the config at bStats.txt and setting enabled to false.");
        }

        if ($this->config->isEnabled()) {
            $this->startSubmitting();
        }
    }

    public function addCustomChart(CustomChart $chart): void
    {
        $this->customCharts[] = $chart;
    }

    private function startSubmitting(): void
    {
        // Many servers tend to restart at a fixed time at xx:00 which causes an uneven
        // distribution of requests on the bStats backend. To circumvent this problem,
        // we introduce some randomness into the initial and second delay.
        // WARNING: You must not modify any part of this Metrics class, including the
        // submit delay or frequency!
        // WARNING: Modifying this code will get your plugin banned on bStats. Just don't do it!

        // Initial delay: 3-6 minutes (in ticks, 20 ticks = 1 second)
        $initialDelay = random_int(3 * 60 * 20, 6 * 60 * 20);
        // Second delay: 0-30 minutes
        $secondDelay = random_int(0, 30 * 60 * 20);
        // Repeat interval: 30 minutes
        $repeatInterval = 30 * 60 * 20;

        $this->plugin->getScheduler()->scheduleDelayedTask(
            new ClosureTask(function (): void {
                $this->submitData();
            }),
            $initialDelay
        );

        $this->plugin->getScheduler()->scheduleDelayedRepeatingTask(
            new ClosureTask(function (): void {
                $this->submitData();
            }),
            $initialDelay + $secondDelay,
            $repeatInterval
        );
    }

    private function submitData(): void
    {
        if (!$this->config->isEnabled() || !$this->plugin->isEnabled()) {
            return;
        }

        $server = $this->plugin->getServer();
        $logger = $this->plugin->getLogger();
        $logErrors = $this->config->isLogErrorsEnabled();
        $logSentData = $this->config->isLogSentDataEnabled();
        $logResponseStatusText = $this->config->isLogResponseStatusTextEnabled();

        // Collect chart data on the main thread
        $chartData = [];
        foreach ($this->customCharts as $chart) {
            $chartJson = $chart->getRequestJsonArray($logger, $logErrors);
            if ($chartJson !== null) {
                $chartData[] = $chartJson;
            }
        }

        // Build the data payload
        $data = [
            "serverUUID" => $this->config->getServerUUID(),
            "metricsVersion" => self::METRICS_VERSION,
            "playerAmount" => count($server->getOnlinePlayers()),
            "onlineMode" => $server->getOnlineMode() ? 1 : 0,
            "pocketmineVersion" => $server->getPocketMineVersion(),
            "pocketmineName" => $server->getName(),
            "phpVersion" => phpversion(),
            "osName" => php_uname("s"),
            "osArch" => php_uname("m"),
            "osVersion" => php_uname("r"),
            "coreCount" => Utils::getCoreCount(),
            "service" => [
                "id" => $this->serviceId,
                "customCharts" => $chartData,
                "pluginVersion" => $this->plugin->getDescription()->getVersion(),
            ],
        ];

        $jsonData = json_encode($data);
        if ($jsonData === false) {
            return;
        }

        if ($logSentData) {
            $logger->info("Sent bStats metrics data: " . $jsonData);
        }

        $server->getAsyncPool()->submitTask(new SubmitDataTask($jsonData, $logErrors, $logResponseStatusText));
    }
}