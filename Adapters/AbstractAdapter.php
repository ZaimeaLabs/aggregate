<?php

declare(strict_types=1);

namespace ZaimeaLabs\Aggregate\Adapters;

abstract class AbstractAdapter
{
    /**
     * Define the time format.
     *
     * @param  string  $column
     * @param  string  $interval
     * @return string
     */
    abstract public function format(string $column, string $interval): string;

    /**
     * Define the sum Time format.
     *
     * @param  string  $column
     * @return string
     */
    abstract public function sumTime(string $column): string;

    /**
     * Define the cumulative format.
     *
     * @param  string  $column
     * @param  string  $dateAlias
     * @return string
     */
    abstract public function cumulative(string $column, string $dateAlias): string;

    /**
     * Define the cumulative Time format.
     *
     * @param  string  $column
     * @param  string  $dateAlias
     * @return string
     */
    abstract public function cumulativeTime(string $column, string $dateAlias): string;
}
