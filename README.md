<p align="center">
  <a href="https://zaimea.com/" target="_blank">
    <img src=".github/aggregate.svg" alt="Aggregate" width="300">
  </a>
</p>
<p align="center">
  Generate aggregate for your models to generate charts or reports in your application.
<p>
<p align="center">
    <a href="https://github.com/zaimealabs/aggregate/actions/workflows/aggregate-tests.yml"><img src="https://github.com/zaimealabs/aggregate/actions/workflows/aggregate-tests.yml/badge.svg" alt="Aggregate Tests"></a>
    <a href="https://github.com/zaimealabs/aggregate/blob/main/LICENSE"><img src="https://img.shields.io/badge/License-Mit-brightgreen.svg" alt="License"></a>
</p>
<div align="center">
  Hey 👋 thanks for considering making a donation, with these donations I can continue working to contribute to ZaimeaLabs projects.
  
  [![Donate](https://img.shields.io/badge/Via_PayPal-blue)](https://www.paypal.com/donate/?hosted_button_id=V6YPST5PUAUKS)
</div>

## Usage

To generate a aggregate for your model, import the `ZaimeaLabs\Aggregate\Aggregate` class and pass along a model or query.
`::model()` come as it is.
`::query()` allow you to use additional filter like `where()`.

```php
// Totals per month
    $model = Aggregate::model(User::class)
        ->between(
            start: now()->startOfYear(),
            end: now()->endOfYear(),
        )
        ->perMonth()
        ->count();

// Count users register per year, results are grouped per month
    $query = Aggregate::query(User::whereNotNull('email_verified_at'))
        ->between(
            start: now()->startOfYear()->subYears(10),
            end: now()->endOfYear(),
        )
        ->perYear()
        ->count();

// Average user calendar activity where record type is work with a over a span of 11 years, results are grouped per year
    $query = Aggregate::query(Record::where('type', 'work'))
        ->dateColumn('scheduled_at')
        ->between(
            start: now()->startOfYear()->subYears(10),
            end: now()->endOfYear())
        ->perYear()
        ->sumTime('duration');
```

## Date Column

If your column is not `created_at` just use `dateColumn('pointed_date_column')`
```php
->dateColumn('pointed_date_column')
```

### Aggregates
***You can use the following aggregates:***  
Function             | Parameter
-------------------- | -------------
`->average()`        | `'column'`
`->count()`          | `'*'`
`->cumulative()`     | `'column'` MySql Only
`->cumulativeTime()` | `'column'` MySql Only
`->max()`            | `'column'`
`->min()`            | `'column'`
`->sum()`            | `'column'`
`->sumTime()`        | `'column'`

### Intervals
***You can use the following aggregates intervals:***
Function        | Description
--------------- | -------------
`->perMinute()` | `set interval per minute`
`->perHour()`   | `set interval per hour`
`->perDay()`    | `set interval per day`
`->perMonth()`  | `set interval per month`
`->perYear()`   | `set interval per year`
