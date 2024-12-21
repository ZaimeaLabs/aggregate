<?php

declare(strict_types=1);

namespace ZaimeaLabs\Aggregate\Adapters;

use Error;

class MySqlAdapter extends AbstractAdapter
{
    /**
     * Define the format for MySql.
     *
     * @param  string  $column
     * @param  string  $interval
     * @return string
     */
    public function format(string $column, string $interval): string
    {
        $format = match ($interval) {
            'minute' => '%Y-%m-%d %H:%i:00',
            'hour' => '%Y-%m-%d %H:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => throw new Error('Invalid interval.'),
        };

        return "date_format({$column}, '{$format}')";
    }

    /**
     * Define the sum Time format for MySql.
     *
     * @param  string  $column
     * @return string
     */
    public function sumTime(string $column): string
    {
        return "TIME_TO_SEC({$column})";
    }

    /**
     * Define the cumulative format for MySql.
     *
     * @param  string  $column
     * @param  string  $dateAlias
     * @return string
     */
    public function cumulative(string $column, string $dateAlias): string
    {
        return "SUM({$column})) OVER (ORDER BY `{$dateAlias}`";
    }

    /**
     * Define the cumulative time format for MySql.
     *
     * @param  string  $column
     * @param  string  $dateAlias
     * @return string
     */
    public function cumulativeTime(string $column, string $dateAlias): string
    {
        return "SUM(TIME_TO_SEC({$column}))) OVER (ORDER BY `{$dateAlias}`";
    }
}
