<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_ab9d215404d83cdb\bStats\PocketmineMp\charts;

use Closure;

class AdvancedPie extends CustomChart
{
    /** @var Closure(): array<string, int> */
    private Closure $callable;

    /**
     * @param Closure(): array<string, int> $callable
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
        $allSkipped = true;
        $values = [];
        foreach ($map as $key => $value) {
            if ($value === 0) {
                continue;
            }
            $allSkipped = false;
            $values[$key] = $value;
        }
        if ($allSkipped) {
            return null;
        }
        return ["values" => $values];
    }
}