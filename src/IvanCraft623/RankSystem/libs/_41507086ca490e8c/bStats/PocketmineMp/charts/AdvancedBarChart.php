<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_41507086ca490e8c\bStats\PocketmineMp\charts;

use Closure;

class AdvancedBarChart extends CustomChart
{
    /** @var Closure(): array<string, list<int>> */
    private Closure $callable;

    /**
     * @param Closure(): array<string, list<int>> $callable
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
            if (count($value) === 0) {
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