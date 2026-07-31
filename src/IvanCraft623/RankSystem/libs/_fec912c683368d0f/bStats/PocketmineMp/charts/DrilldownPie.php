<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_fec912c683368d0f\bStats\PocketmineMp\charts;

use Closure;

class DrilldownPie extends CustomChart
{
    /** @var Closure(): array<string, array<string, int>> */
    private Closure $callable;

    /**
     * @param Closure(): array<string, array<string, int>> $callable
     */
    public function __construct(string $chartId, Closure $callable)
    {
        parent::__construct($chartId);
        $this->callable = $callable;
    }

    protected function getChartData(): ?array
    {
        $map = ($this->callable)();
        if (count($map) === 0) {
            return null;
        }
        $reallyAllSkipped = true;
        $values = [];
        foreach ($map as $key => $innerMap) {
            if (count($innerMap) === 0) {
                continue;
            }
            $reallyAllSkipped = false;
            $values[$key] = $innerMap;
        }
        if ($reallyAllSkipped) {
            return null;
        }
        return ["values" => $values];
    }
}