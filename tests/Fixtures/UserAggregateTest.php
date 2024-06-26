<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class UserAggregateTest extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 't', 'd', 'created_at'];
}
