<?php

declare(strict_types=1);

namespace Zaimea\Aggregate\Adapters;

use Error;

class PgsqlAdapter extends AbstractAdapter
{
    /**
     * Define the format for Pgsql.
     *
     * @param  string  $column
     * @param  string  $interval
     * @return string
     */
    public function format(string $column, string $interval): string
    {
        $format = match ($interval) {
            'minute' => 'YYYY-MM-DD HH24:MI:00',
            'hour' => 'YYYY-MM-DD HH24:00:00',
            'day' => 'YYYY-MM-DD',
            'week' => 'IYYY-IW',
            'month' => 'YYYY-MM',
            'year' => 'YYYY',
            default => throw new Error('Invalid interval.'),
        };

        return "to_char(\"{$column}\", '{$format}')";
    }

    /**
     * Define the sum Time format for Pgsql.
     *
     * @param  string  $column
     * @return string
     */
    public function sumTime(string $column): string
    {
        return "EXTRACT(EPOCH FROM \"{$column}\")";
    }

    /**
     * Define the cumulative format for Pgsql.
     *
     * @param  string  $column
     * @param  string  $dateAlias
     * @return string
     */
    public function cumulative(string $column, string $dateAlias): string
    {
        return throw new Error('Pgsql not done for cumulative.');
    }

    /**
     * Define the cumulative time format for Pgsql.
     *
     * @param  string  $column
     * @param  string  $dateAlias
     * @return string
     */
    public function cumulativeTime(string $column, string $dateAlias): string
    {
        return throw new Error('Pgsql not done for cumulativeTime.');
    }
}
