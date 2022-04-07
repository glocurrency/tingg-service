<?php

namespace GloCurrency\Tingg\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GloCurrency\Tingg\Tingg;
use GloCurrency\Tingg\Events\TransactionUpdatedEvent;
use GloCurrency\Tingg\Events\TransactionCreatedEvent;
use GloCurrency\Tingg\Enums\TransactionStateCodeEnum;
use GloCurrency\Tingg\Database\Factories\TransactionFactory;
use GloCurrency\MiddlewareBlocks\Contracts\ModelWithStateCodeInterface;
use BrokeYourBike\Tingg\Interfaces\PaymentInterface;
use BrokeYourBike\Tingg\Enums\PaymentStatusCodeEnum;
use BrokeYourBike\HasSourceModel\SourceModelInterface;
use BrokeYourBike\CountryCasts\Alpha2Cast;
use BrokeYourBike\BaseModels\BaseUuid;

/**
 * GloCurrency\Tingg\Models\Transaction
 *
 * @property string $id
 * @property string $transaction_id
 * @property string $processing_item_id
 * @property \GloCurrency\Tingg\Enums\TransactionStateCodeEnum $state_code
 * @property string|null $state_code_reason
 * @property \BrokeYourBike\Tingg\Enums\PaymentStatusCodeEnum|null $error_code
 * @property string|null $error_code_description
 * @property string $reference
 * @property string $remote_reference
 * @property string $country_code
 * @property string $country_code_alpha2
 * @property string $currency_code
 * @property float $amount
 * @property string $service_code
 * @property string|null $product_code
 * @property string $sender_name
 * @property string $sender_phone_number
 * @property string $recipient_phone_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Transaction extends BaseUuid implements ModelWithStateCodeInterface, SourceModelInterface, PaymentInterface
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tingg_transactions';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<mixed>
     */
    protected $casts = [
        'state_code' => TransactionStateCodeEnum::class,
        'error_code' => PaymentStatusCodeEnum::class,
        'country_code_alpha2' => Alpha2Cast::class . ':country_code',
        'receive_amount' => 'double',
    ];

    /**
     * @var array<mixed>
     */
    protected $dispatchesEvents = [
        'created' => TransactionCreatedEvent::class,
        'updated' => TransactionUpdatedEvent::class,
    ];

    public function getStateCode(): TransactionStateCodeEnum
    {
        return $this->state_code;
    }

    public function getStateCodeReason(): ?string
    {
        return $this->state_code_reason;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getRemoteReference(): string
    {
        return $this->remote_reference;
    }

    public function getCountryCodeAlpha2(): string
    {
        return $this->country_code_alpha2;
    }

    public function getCurrencyCode(): string
    {
        return $this->currency_code;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getServiceCode(): string
    {
        return $this->service_code;
    }

    public function getProductCode(): ?string
    {
        return $this->product_code;
    }

    public function getSenderPhoneNumber(): string
    {
        return $this->sender_phone_number;
    }

    public function getSenderName(): string
    {
        return $this->sender_name;
    }

    public function getRecipientPhoneNumber(): string
    {
        return $this->recipient_phone_number;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->created_at ?? now();
    }

    /**
     * The ProcessingItem that Transaction belong to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function processingItem()
    {
        return $this->belongsTo(Tingg::$processingItemModel, 'processing_item_id', 'id');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return TransactionFactory::new();
    }
}
