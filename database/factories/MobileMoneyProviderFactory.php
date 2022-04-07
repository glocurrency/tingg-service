<?php

namespace GloCurrency\Tingg\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use GloCurrency\Tingg\Tingg;
use GloCurrency\Tingg\Models\MobileMoneyProvider;

class MobileMoneyProviderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MobileMoneyProvider::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'mobile_money_provider_id' => (Tingg::$mobileMoneyProviderModel)::factory(),
            'code' => $this->faker->numerify('#######'),
        ];
    }
}
