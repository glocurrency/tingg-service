<?php

namespace GloCurrency\Tingg\Tests\Fixtures;

use Orchestra\Testbench\Factories\UserFactory;
use Illuminate\Foundation\Auth\User as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProcessingItemFixture extends Model
{
    use HasFactory;

    protected $table = 'users';

    protected $guarded = [];

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
