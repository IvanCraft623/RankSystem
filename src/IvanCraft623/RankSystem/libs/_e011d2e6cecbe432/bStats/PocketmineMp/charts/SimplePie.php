<?php

declare(strict_types=1);

namespace IvanCraft623\RankSystem\libs\_e011d2e6cecbe432\bStats\PocketmineMp\charts;

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