<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_8f07b1a4a0e2ea16\bStats\PocketmineMp\charts;

use Closure;

class SingleLineChart extends CustomChart
{
    /** @var Closure(): int */
    private Closure $callable;

    /**
     * @param Closure(): int $callable
     */
    public function __construct(string $chartId, Closure $callable)
    {
        parent::__construct($chartId);
        $this->callable = $callable;
    }

    protected function getChartData(): ?array
    {
        $value = ($this->callable)();
        if ($value === 0) {
            return null;
        }
        return ["value" => $value];
    }
}