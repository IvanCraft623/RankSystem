<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_3d291e54ddfd903d\bStats\PocketmineMp\charts;

abstract class CustomChart
{
    private string $chartId;

    protected function __construct(string $chartId)
    {
        $this->chartId = $chartId;
    }

    /**
     * @return array{chartId: string, data: array<mixed>}|null
     */
    public function getRequestJsonArray(\Logger $logger, bool $logErrors): ?array
    {
        try {
            $data = $this->getChartData();
            if ($data === null) {
                return null;
            }
            return [
                "chartId" => $this->chartId,
                "data" => $data,
            ];
        } catch (\Throwable $t) {
            if ($logErrors) {
                $logger->warning("Failed to get data for custom chart with id " . $this->chartId);
                $logger->logException($t);
            }
            return null;
        }
    }

    /**
     * @return array<mixed>|null
     */
    abstract protected function getChartData(): ?array;
}