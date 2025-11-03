<?php

declare(strict_types=1);

namespace Tests;

use Tests\Fixtures\UserAggregateTest;
use Tests\TestCase;
use Zaimea\Aggregate\Aggregate;

class AggregatesTest extends TestCase
{
    public function test_it_can_get_perMonth_with_interval_one_year()
    {
        UserAggregateTest::create(['name' => 'Custura Laurentiu', 'created_at' => now()]);

        $model = Aggregate::model(UserAggregateTest::class)
                        ->between(
                            start: now()->startOfYear(),
                            end: now()->endOfYear(),
                        )
                        ->perMonth()
                        ->count();

        $this->assertSame(12, $model->count());
    }

    public function test_it_can_average()
    {
        UserAggregateTest::create(['name' => 'name-1', 't' => -10, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-2', 't' => -10, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-3', 't' => 0, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-4', 't' => +10, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-5', 't' => +20, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-6', 't' => null]);

        $model = Aggregate::model(UserAggregateTest::class)
                        ->between(
                            start: now()->startOfDay(),
                            end: now()->endOfDay(),
                        )
                        ->perMonth()
                        ->average('t');

        $model2 = Aggregate::query(UserAggregateTest::where('name', 'name-1'))
                            ->between(
                                start: now()->startOfDay(),
                                end: now()->endOfDay(),
                            )
                            ->perMonth()
                            ->average('t');

        $this->assertNull(UserAggregateTest::query()->where('name', 'no-name')->avg('t'));
        $this->assertEquals(-10, $model2->first()->aggregate);
        $this->assertEquals(2, $model->first()->aggregate);
    }

    public function test_it_can_count()
    {
        UserAggregateTest::create(['name' => 'name-1', 't' => -10, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-2', 't' => -10, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-3', 't' => 0, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-4', 't' => +10, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-5', 't' => +20, 'created_at' => now()]);

        $model = Aggregate::model(UserAggregateTest::class)
                        ->between(
                            start: now()->startOfMonth(),
                            end: now()->endOfMonth(),
                        )
                        ->perMonth()
                        ->count();

        $this->assertSame(5, $model->first()->aggregate);
    }

    public function test_it_can_cumulative()
    {
        UserAggregateTest::create(['name' => 'name-1', 't' => 10, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-2', 't' => 10, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-3', 't' => 0, 'created_at' => now()->addDay()]);
        UserAggregateTest::create(['name' => 'name-4', 't' => 10, 'created_at' => now()->addDays(2)]);
        UserAggregateTest::create(['name' => 'name-5', 't' => 20, 'created_at' => now()->addDays(2)]);

        $model = Aggregate::model(UserAggregateTest::class)
                        ->between(
                            start: now(),
                            end: now()->addDays(2),
                        )
                        ->perDay()
                        ->cumulative('t');
        $first = $model->first();
        $second = $model->skip('1')->first();
        $last = $model->last();

        $this->assertSame(20, $first->aggregate);
        $this->assertSame(20, $second->aggregate);
        $this->assertSame(50, $last->aggregate);
    }

    public function test_it_can_cumulativeTime()
    {
        UserAggregateTest::create(['name' => 'name-1', 'd' => '01:00:00', 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-2', 'd' => '02:00:00', 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-3', 'd' => '03:00:00', 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-4', 'd' => '04:00:00', 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-5', 'd' => '05:00:00', 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-6', 'd' => '01:00:00', 'created_at' => now()->addDay()]);
        UserAggregateTest::create(['name' => 'name-7', 'd' => '01:00:00', 'created_at' => now()->addDay()]);
        UserAggregateTest::create(['name' => 'name-8', 'd' => '01:00:00', 'created_at' => now()->addDay()]);

        $model = Aggregate::model(UserAggregateTest::class)
                        ->between(
                            start: now()->startOfDay(),
                            end: now()->addDay()->endOfDay(),
                        )
                        ->perDay()
                        ->cumulativeTime('d');

        $this->assertSame(15.0, $model->first()->aggregate);
        $this->assertSame(18.0, $model->last()->aggregate);
    }

    public function test_it_can_min_max()
    {
        UserAggregateTest::create(['name' => 'name-1', 't' => -1, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-2', 't' => -1, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-3', 't' => 0, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-4', 't' => +1, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-5', 't' => +2, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-6', 't' => null, 'created_at' => now()]);

        $model = Aggregate::model(UserAggregateTest::class)
                        ->between(
                            start: now()->startOfDay(),
                            end: now()->endOfDay(),
                        )
                        ->perMonth()
                        ->min('t');

        $model2 = Aggregate::model(UserAggregateTest::class)
                            ->between(
                                start: now()->startOfDay(),
                                end: now()->endOfDay(),
                            )
                            ->perMonth()
                            ->max('t');

        $this->assertEquals(-1, $model->first()->aggregate);
        $this->assertEquals(2, $model2->first()->aggregate);
    }

    public function test_it_can_sum()
    {
        UserAggregateTest::create(['name' => 'name-1', 't' => -11, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-2', 't' => -10, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-3', 't' => 0, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-4', 't' => +12, 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-5', 't' => null, 'created_at' => now()]);

        $model = Aggregate::model(UserAggregateTest::class)
                        ->between(
                            start: now()->startOfDay(),
                            end: now()->endOfDay(),
                        )
                        ->perDay()
                        ->sum('t');

        $model2 = Aggregate::query(UserAggregateTest::where('name', 'no-name'))
                        ->between(
                            start: now()->startOfDay(),
                            end: now()->endOfDay(),
                        )
                        ->perDay()
                        ->sum('t');

        $model3 = Aggregate::query(UserAggregateTest::where('id', '>', 1))
                        ->between(
                            start: now()->startOfDay(),
                            end: now()->endOfDay(),
                        )
                        ->perDay()
                        ->sum('t');

        $this->assertEquals(-9, $model->first()->aggregate);
        $result = $model2->first()->aggregate;
        $this->assertNotNull($result);
        $this->assertEquals(0, $result);
        $this->assertEquals(2, $model3->first()->aggregate);
    }

    public function test_it_can_sumTime()
    {
        UserAggregateTest::create(['name' => 'name-1', 'd' => '01:00:00', 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-2', 'd' => '02:00:00', 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-3', 'd' => '03:00:00', 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-4', 'd' => '04:00:00', 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-5', 'd' => '05:00:00', 'created_at' => now()]);
        UserAggregateTest::create(['name' => 'name-6', 'd' => '01:00:00', 'created_at' => now()->addDay()]);
        UserAggregateTest::create(['name' => 'name-7', 'd' => '01:00:00', 'created_at' => now()->addDay()]);
        UserAggregateTest::create(['name' => 'name-8', 'd' => '01:00:00', 'created_at' => now()->addDay()]);

        $model = Aggregate::model(UserAggregateTest::class)
                        ->between(
                            start: now()->startOfDay(),
                            end: now()->addDay()->endOfDay(),
                        )
                        ->perDay()
                        ->sumTime('d');

        $model2 = Aggregate::model(UserAggregateTest::class)
                        ->between(
                            start: now()->startOfDay(),
                            end: now()->addDay()->endOfDay(),
                        )
                        ->perMonth()
                        ->sumTime('d');

        $this->assertSame(15.0, $model->first()->aggregate);
        $this->assertSame(3.0, $model->last()->aggregate);
        $this->assertSame(18.0, $model2->first()->aggregate);
    }
}
