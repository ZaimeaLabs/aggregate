<?php

declare(strict_types=1);

namespace ZaimeaLabs\Aggregate\Adapters;

use Error;

class SqlServerAdapter extends AbstractAdapter
{
    /**
     * Define the format for SqlServer.
     *
     * @param  string  $column
     * @param  string  $interval
     * @return string
     */
    public function format(string $column, string $interval): string
    {
        $format = match ($interval) {
            'minute' => 'yyyy-MM-dd HH:mm:00',
            'hour' => 'yyyy-MM-dd HH:00',
            'day' => 'yyyy-MM-dd',
            'month' => 'yyyy-MM',
            'year' => 'yyyy',
            default => throw new Error('Invalid interval.'),
        };

        return "FORMAT({$column}, '{$format}')";
    }

    /**
     * Define the sum Time format for Sql Server.
     *
     * @param  string  $column
     * @return string
     */
    public function sumTime(string $column): string
    {
        return "DATEDIFF(second,0,{$column})";
    }

    /**
     * Define the cumulative format for Sql Server.
     *
     * @param  string  $column
     * @param  string  $dateAlias
     * @return string
     */
    public function cumulative(string $column, string $dateAlias): string
    {
        return throw new Error('Sql Server not done for cumulative.');
    }

    /**
     * Define the cumulative time format for Sql Server.
     *
     * @param  string  $column
     * @param  string  $dateAlias
     * @return string
     */
    public function cumulativeTime(string $column, string $dateAlias): string
    {
        return throw new Error('Sql Server not done for cumulativeTime.');
    }
}
