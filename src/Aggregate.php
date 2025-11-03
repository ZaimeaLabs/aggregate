<?php

declare(strict_types=1);

namespace Zaimea\Aggregate;

use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Error;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Aggregate
{
    /**
     * The interval of query.
     *
     * @var string
     */
    public string $interval;

    /**
     * The start date of query.
     *
     * @var \Carbon\CarbonInterface
     */
    public CarbonInterface $start;

    /**
     * The end date of query.
     *
     * @var \Carbon\CarbonInterface
     */
    public CarbonInterface $end;

    /**
     * The date column of query.
     *
     * @var string
     */
    public string $dateColumn = 'created_at';

    /**
     * The date alias of query.
     *
     * @var string
     */
    public string $dateAlias = 'date';

    /**
     * Set cumulative if query need.
     *
     * @var bool
     */
    public bool $cumulative = false;

    /**
     * Create a new query builder instance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     */
    public function __construct(public Builder $builder)
    {
        //
    }

    /**
     * Begin querying the model.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function query(Builder $builder): self
    {
        return new static($builder);
    }

    /**
     * Begin the model.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function model(string $model): self
    {
        return new static($model::query());
    }

    /**
     * Set query between dates.
     *
     * @param \Carbon\CarbonInterface $start
     * @param \Carbon\CarbonInterface $end
     * @return self
     */
    public function between($start, $end): self
    {
        $this->start = $start;
        $this->end = $end;

        return $this;
    }

    /**
     * Set the query interval.
     *
     * @param string $interval
     * @return self
     */
    public function interval(string $interval): self
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * Set the interval per minutes.
     *
     * @return self
     */
    public function perMinute(): self
    {
        return $this->interval('minute');
    }

    /**
     * Set the interval per hours.
     *
     * @return self
     */
    public function perHour(): self
    {
        return $this->interval('hour');
    }

    /**
     * Set the interval per days.
     *
     * @return self
     */
    public function perDay(): self
    {
        return $this->interval('day');
    }

    /**
     * Set the interval per weeks.
     *
     * @return self
     */
    public function perWeek(): self
    {
        return $this->interval('week');
    }

    /**
     * Set the interval per months.
     *
     * @return self
     */
    public function perMonth(): self
    {
        return $this->interval('month');
    }

    /**
     * Set the interval per years.
     *
     * @return self
     */
    public function perYear(): self
    {
        return $this->interval('year');
    }

    /**
     * Set the date column name.
     *
     * @param  string  $column
     * @return self
     */
    public function dateColumn(string $column): self
    {
        $this->dateColumn = $column;

        return $this;
    }

    /**
     * Set the date column name.
     *
     * @param  string  $column
     * @return self
     */
    public function dateAlias(string $alias): self
    {
        $this->dateAlias = $alias;

        return $this;
    }

    /**
     * Build aggregate result for query.
     *
     * @param  string  $column
     * @param  string  $aggregate
     * @return \Illuminate\Support\Collection
     */
    public function aggregate(string $column, string $aggregate): Collection
    {
        $values = $this->builder
            ->toBase()
            ->selectRaw("
                {$this->getSqlDate()} as {$this->dateAlias},
                {$aggregate}({$column}) as aggregate
            ")
            ->whereBetween(
                "{$this->builder->getModel()->getTable()}.$this->dateColumn",
                [$this->start, $this->end]
            )
            ->groupBy($this->dateAlias)
            ->when(! $this->cumulative, function ($query) {
                $query->orderBy($this->dateAlias);
            })
            ->get();

        return $this->cumulative
                ? $this->mapValuesToDatesCumulative($values)
                : $this->mapValuesToDates($values);
    }

    /**
     * Set aggregate to Average.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Collection
     */
    public function average(string $column): Collection
    {
        return $this->aggregate($column, 'avg');
    }

    /**
     * Set aggregate to Cumulative.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Collection
     */
    public function cumulative(string $column): Collection
    {
        $this->cumulative = true;

        return $this->aggregate(
            $this->getSqlAdapter()
            ->cumulative($column, $this->dateAlias),
            'sum'
        );
    }

    /**
     * Set aggregate to Cumulative Sum time to sec.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Collection
     */
    public function cumulativeTime(string $column): Collection
    {
        $this->cumulative = true;

        return $this->aggregate(
            $this->getSqlAdapter()
            ->cumulativeTime($column, $this->dateAlias),
            'sum'
        );
    }

    /**
     * Set aggregate to Minimum.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Collection
     */
    public function min(string $column): Collection
    {
        return $this->aggregate($column, 'min');
    }

    /**
     * Set aggregate to Maximum.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Collection
     */
    public function max(string $column): Collection
    {
        return $this->aggregate($column, 'max');
    }

    /**
     * Set aggregate to Sum.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Collection
     */
    public function sum(string $column): Collection
    {
        return $this->aggregate($column, 'sum');
    }

    /**
     * Set aggregate to Sum time to seconds.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Collection
     */
    public function sumTime(string $column): Collection
    {
        return $this->aggregate(
            $this->getSqlAdapter()
            ->sumTime($column),
            'sum'
        );
    }

    /**
     * Set aggregate to Count.
     *
     * @param  string  $column
     * @return \Illuminate\Support\Collection
     */
    public function count(string $column = '*'): Collection
    {
        return $this->aggregate($column, 'count');
    }

    /**
     * Map values to dates.
     *
     * @param  Collection  $values
     * @return \Illuminate\Support\Collection
     */
    public function mapValuesToDates(Collection $values): Collection
    {
        $placeholders = $this->getDatePeriod()->map(
            function (CarbonInterface $date) use ($values) {
                return collect($values->first())
                                ->put('date', $date->format($this->getCarbonDateFormat()))
                                ->put('aggregate', "0")->toArray();
            }
        );

        $merge = new Collection();

        $placeholders->each(function ($place) use ($merge){
            $merge->push((object)$place);
        });

        return $values
            ->merge($merge)
            ->unique('date')
            ->sortBy('date')
            ->flatten();
    }

    /**
     * Map values to dates cumulative.
     *
     * @param  Collection  $values
     * @return \Illuminate\Support\Collection
     */
    public function mapValuesToDatesCumulative(Collection $values): Collection
    {
        $previous = null;
        $mapValues = [];

        foreach ($this->getDatePeriod() as $period) {
            $period = $period->format($this->getCarbonDateFormat());
            $value = $values->firstWhere($this->dateAlias, $period);
            $previous = data_get($value,'aggregate',$previous);
            if($value == null) {
                $mapValues[] = collect($values->first())
                                    ->put('date', $period)
                                    ->put('aggregate', $previous)->toArray();
            } else {
                $mapValues[] = collect($value)->toArray();
            }
        }

        $merge = new Collection();

        collect($mapValues)->each(function ($place) use ($merge){
            $merge->push((object)$place);
        });

        return collect($merge)
            ->unique('date')
            ->sortBy('date')
            ->flatten();
    }

    /**
     * Get the date range for query.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getDatePeriod(): Collection
    {
        return collect(
            CarbonPeriod::between(
                $this->start,
                $this->end,
            )->interval("1 {$this->interval}")
        );
    }

    /**
     * Get sql date format for selectRaw.
     *
     * @return string
     */
    protected function getSqlDate(): string
    {
        return $this->getSqlAdapter()->format("{$this->builder->getModel()->getTable()}.$this->dateColumn", $this->interval);
    }

    /**
     * Get Carbon date format.
     *
     * @return string
     */
    protected function getCarbonDateFormat(): string
    {
        return match ($this->interval) {
            'minute' => 'Y-m-d H:i:00',
            'hour' => 'Y-m-d H:00',
            'day' => 'Y-m-d',
            'week' => 'Y-W',
            'month' => 'Y-m',
            'year' => 'Y',
            default => throw new Error('Invalid interval.'),
        };
    }

    /**
     * Get sql adapter.
     *
     * @return mixed
     */
    protected function getSqlAdapter(): mixed
    {
        $adapter = match ($this->builder->getConnection()->getDriverName()) {
            'mysql', 'mariadb' => new Adapters\MySqlAdapter(),
            'sqlite' => new Adapters\SqliteAdapter(),
            'pgsql' => new Adapters\PgsqlAdapter(),
            default => throw new Error('Unsupported database driver.'),
        };

        return $adapter;
    }
}
