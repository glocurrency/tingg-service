<?php

namespace Tests\Unit\Enums\Tingg;

use GloCurrency\Tingg\Tests\TestCase;
use GloCurrency\Tingg\Enums\TransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Enums\ProcessingItemStateCodeEnum as MProcessingItemStateCodeEnum;
use BrokeYourBike\Tingg\Enums\PaymentStatusCodeEnum;
use BrokeYourBike\Tingg\Enums\AuthCodeEnum;

class TransactionStateCodeTest extends TestCase
{
    /** @test */
    public function it_can_return_processing_item_state_code_from_all_values()
    {
        foreach (TransactionStateCodeEnum::cases() as $value) {
            $this->assertInstanceOf(MProcessingItemStateCodeEnum::class, $value->getProcessingItemStateCode());
        }
    }

    /** @test */
    public function it_can_be_created_from_auth_code()
    {
        foreach (AuthCodeEnum::cases() as $value) {
            $this->assertInstanceOf(TransactionStateCodeEnum::class, TransactionStateCodeEnum::makeFromAuthCode($value));
        }
    }

    /** @test */
    public function it_can_be_created_from_status_code()
    {
        foreach (PaymentStatusCodeEnum::cases() as $value) {
            $this->assertInstanceOf(TransactionStateCodeEnum::class, TransactionStateCodeEnum::makeFromPaymentStatusCode($value));
        }
    }
}
