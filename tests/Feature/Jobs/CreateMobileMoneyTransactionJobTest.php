<?php

namespace GloCurrency\Tingg\Tests\Feature\Jobs;

use Money\Money;
use Money\Currency;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Event;
use GloCurrency\Tingg\Tingg;
use GloCurrency\Tingg\Tests\FeatureTestCase;
use GloCurrency\Tingg\Models\Transaction;
use GloCurrency\Tingg\Models\MobileMoneyProvider;
use GloCurrency\Tingg\Jobs\CreateMobileMoneyTransactionJob;
use GloCurrency\Tingg\Exceptions\CreateTransactionException;
use GloCurrency\Tingg\Events\TransactionUpdatedEvent;
use GloCurrency\Tingg\Events\TransactionCreatedEvent;
use GloCurrency\Tingg\Enums\TransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Enums\TransactionTypeEnum as MTransactionTypeEnum;
use GloCurrency\MiddlewareBlocks\Enums\TransactionStateCodeEnum as MTransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Contracts\TransactionInterface as MTransactionInterface;
use GloCurrency\MiddlewareBlocks\Contracts\SenderInterface as MSenderInterface;
use GloCurrency\MiddlewareBlocks\Contracts\RecipientInterface as MRecipientInterface;
use GloCurrency\MiddlewareBlocks\Contracts\ProcessingItemInterface as MProcessingItemInterface;

class CreateMobileMoneyTransactionJobTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TransactionCreatedEvent::class,
            TransactionUpdatedEvent::class,
        ]);

        Notification::fake();
    }

    /** @test */
    public function it_will_throw_without_transaction(): void
    {
        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn(null);

        $this->expectExceptionMessage("transaction not found");
        $this->expectException(CreateTransactionException::class);

        CreateMobileMoneyTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_if_target_transaction_already_exist(): void
    {
        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::MOBILE);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /** @var MTransactionInterface $transaction */
        $targetTransaction = Transaction::factory()->create([
            'transaction_id' => $transaction->getId(),
        ]);

        $this->expectExceptionMessage("Transaction cannot be created twice, `{$targetTransaction->id}`");
        $this->expectException(CreateTransactionException::class);

        CreateMobileMoneyTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_transaction_sender(): void
    {
        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::MOBILE);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn(null);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        $this->expectExceptionMessage('sender not found');
        $this->expectException(CreateTransactionException::class);

        CreateMobileMoneyTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::MOBILE);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn(null);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        $this->expectExceptionMessage('recipient not found');
        $this->expectException(CreateTransactionException::class);

        CreateMobileMoneyTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_mobile_provider_in_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getMobileProvider')->willReturn(null);

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::MOBILE);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /** @var MRecipientInterface $recipient */
        $this->expectExceptionMessage("`{$recipient->getId()}` has no `mobile_provider`");
        $this->expectException(CreateTransactionException::class);

        CreateMobileMoneyTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_phone_number_in_transaction_recipient(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getMobileProvider')->willReturn($this->faker->word());
        $recipient->method('getPhoneNumber')->willReturn(null);

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::MOBILE);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /** @var MRecipientInterface $recipient */
        $this->expectExceptionMessage("`{$recipient->getId()}` has no `phone_number`");
        $this->expectException(CreateTransactionException::class);

        CreateMobileMoneyTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_mobile_provider_found(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getMobileProvider')->willReturn('fake-provider');
        $recipient->method('getPhoneNumber')->willReturn($this->faker->e164PhoneNumber());
        $recipient->method('getCountryCode')->willReturn('USA');

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::MOBILE);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        $this->assertNull((Tingg::$mobileMoneyProviderModel)::firstWhere([
            'code' => 'fake-provider',
            'country_code' => 'USA',
        ]));

        $this->expectExceptionMessage(Tingg::$mobileMoneyProviderModel);
        $this->expectException(CreateTransactionException::class);

        CreateMobileMoneyTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_will_throw_without_target_mobile_provider_found(): void
    {
        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();
        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getMobileProvider')->willReturn('fake-provider');
        $recipient->method('getPhoneNumber')->willReturn($this->faker->e164PhoneNumber());
        $recipient->method('getCountryCode')->willReturn('USA');

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::MOBILE);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        $provider = (Tingg::$mobileMoneyProviderModel)::factory()->create([
            'code' => 'fake-provider',
            'country_code' => 'USA',
        ]);

        $this->expectExceptionMessage($provider->getId());
        $this->expectException(CreateTransactionException::class);

        CreateMobileMoneyTransactionJob::dispatchSync($processingItem);
    }

    /** @test */
    public function it_can_create_transaction(): void
    {
        config(['services.tingg.sender_name' => 'John Doe']);
        config(['services.tingg.sender_phone_number' => '+12345']);

        $sender = $this->getMockBuilder(MSenderInterface::class)->getMock();

        $recipient = $this->getMockBuilder(MRecipientInterface::class)->getMock();
        $recipient->method('getMobileProvider')->willReturn($this->faker->word());
        $recipient->method('getPhoneNumber')->willReturn($this->faker->e164PhoneNumber());
        $recipient->method('getCountryCode')->willReturn($this->faker->countryISOAlpha3());

        $transaction = $this->getMockBuilder(MTransactionInterface::class)->getMock();
        $transaction->method('getId')->willReturn('1234');
        $transaction->method('getType')->willReturn(MTransactionTypeEnum::MOBILE);
        $transaction->method('getStateCode')->willReturn(MTransactionStateCodeEnum::PROCESSING);
        $transaction->method('getSender')->willReturn($sender);
        $transaction->method('getRecipient')->willReturn($recipient);
        $transaction->method('getOutputAmount')->willReturn(new Money('201', new Currency('NGN')));

        $processingItem = $this->getMockBuilder(MProcessingItemInterface::class)->getMock();
        $processingItem->method('getTransaction')->willReturn($transaction);

        /**
         * @var MSenderInterface $sender
         * @var MRecipientInterface $recipient
         * @var MTransactionInterface $transaction
         * @var MProcessingItemInterface $processingItem
        */

        $provider = (Tingg::$mobileMoneyProviderModel)::factory()->create([
            'code' => $recipient->getMobileProvider(),
            'country_code' => $recipient->getCountryCode(),
        ]);

        $targetProvider = MobileMoneyProvider::factory()->create([
            'mobile_money_provider_id' => $provider->getId(),
            'code' => 'target-provider-code',
        ]);

        $this->assertNull(Transaction::first());

        CreateMobileMoneyTransactionJob::dispatchSync($processingItem);

        $targetTransaction = Transaction::first();
        $this->assertInstanceOf(Transaction::class, $targetTransaction);

        $this->assertSame($transaction->getId(), $targetTransaction->transaction_id);
        $this->assertSame($processingItem->getId(), $targetTransaction->processing_item_id);
        $this->assertEquals(TransactionStateCodeEnum::LOCAL_UNPROCESSED, $targetTransaction->state_code);
        $this->assertSame(2.01, $targetTransaction->amount);
        $this->assertSame($transaction->getOutputAmount()->getCurrency()->getCode(), $targetTransaction->currency_code);
        $this->assertSame($transaction->getReferenceForHumans(), $targetTransaction->reference);
        $this->assertSame($targetProvider->code, $targetTransaction->service_code);
        $this->assertSame('John Doe', $targetTransaction->sender_name);
        $this->assertSame('+12345', $targetTransaction->sender_phone_number);
        $this->assertSame($recipient->getPhoneNumber(), $targetTransaction->recipient_phone_number);
        // TODO: more accertions
    }
}
