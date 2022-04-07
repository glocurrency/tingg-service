<?php

namespace GloCurrency\Tingg\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use GloCurrency\Tingg\Tests\Fixtures\MobileMoneyProviderFixture;

class MobileMoneyProviderFixtureFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MobileMoneyProviderFixture::class;

    /**
     * Define the model's default state.
     *
     * @return array<string,mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'code' => $this->faker->unique()->word(),
            'country_code' => $this->faker->countryISOAlpha3(),
        ];
    }
}
