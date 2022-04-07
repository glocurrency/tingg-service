<?php

namespace GloCurrency\Tingg\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use GloCurrency\Tingg\Tingg;
use GloCurrency\Tingg\Models\Transaction;
use GloCurrency\Tingg\Models\Sender;
use GloCurrency\Tingg\Models\Recipient;
use GloCurrency\Tingg\Models\Bank;
use GloCurrency\Tingg\Enums\TransactionStateCodeEnum;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'transaction_id' => (Tingg::$transactionModel)::factory(),
            'processing_item_id' => (Tingg::$processingItemModel)::factory(),
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
            'reference' => $this->faker->uuid(),
            'country_code' => $this->faker->countryISOAlpha3(),
            'currency_code' => $this->faker->currencyCode(),
            'amount' => $this->faker->randomFloat(2, 1),
            'service_code' => $this->faker->word(),
            'sender_name' => $this->faker->name(),
            'sender_phone_number' => $this->faker->e164PhoneNumber(),
            'recipient_phone_number' => $this->faker->e164PhoneNumber(),
        ];
    }
}
