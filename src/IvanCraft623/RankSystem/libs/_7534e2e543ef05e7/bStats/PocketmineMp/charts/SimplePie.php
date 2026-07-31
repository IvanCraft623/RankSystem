<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_7534e2e543ef05e7\bStats\PocketmineMp\charts;

use Closure;

class SimplePie extends CustomChart
{
    /** @var Closure(): string */
    private Closure $callable;

    /**
     * @param Closure(): string $callable
     */
    public function __construct(string $chartId, Closure $callable)
    {
        parent::__construct($chartId);
        $this->callable = $callable;
    }

    protected function getChartData(): ?array
    {
        $value = ($this->callable)();
        if ($value === "") {
            return null;
        }
        return ["value" => $value];
    }
}