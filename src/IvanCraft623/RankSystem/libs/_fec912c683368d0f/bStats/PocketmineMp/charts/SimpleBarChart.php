<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_fec912c683368d0f\bStats\PocketmineMp\charts;

use Closure;

class SimpleBarChart extends CustomChart
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
        $values = [];
        foreach ($map as $key => $value) {
            $values[$key] = [$value];
        }
        return ["values" => $values];
    }
}