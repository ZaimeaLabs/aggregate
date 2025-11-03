<?php

declare(strict_types=1);

namespace Zaimea\Aggregate\Adapters;

use Error;

class SqliteAdapter extends AbstractAdapter
{
    /**
     * Define the format for Sqlite.
     *
     * @param  string  $column
     * @param  string  $interval
     * @return string
     */
    public function format(string $column, string $interval): string
    {
        $format = match ($interval) {
            'minute' => '%Y-%m-%d %H:%M:00',
            'hour' => '%Y-%m-%d %H:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%W',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => throw new Error('Invalid interval.'),
        };

        return "strftime('{$format}', {$column})";
    }

    /**
     * Define the sum Time format for Sqltile.
     *
     * @param  string  $column
     * @return string
     */
    public function sumTime(string $column): string
    {
        return "time({$column})";
    }

    /**
     * Define the cumulative format for Sqlite.
     *
     * @param  string  $column
     * @param  string  $dateAlias
     * @return string
     */
    public function cumulative(string $column, string $dateAlias): string
    {
        return "SUM({$column})) OVER (ORDER BY '{$dateAlias}' rows between unbounded preceding and current row";
    }

    /**
     * Define the cumulative time format for Sqlite.
     *
     * @param  string  $column
     * @param  string  $dateAlias
     * @return string
     */
    public function cumulativeTime(string $column, string $dateAlias): string
    {
        return "SUM(time({$column}))) OVER (ORDER BY '{$dateAlias}' rows between unbounded preceding and current row";
    }
}
