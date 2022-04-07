<?php

namespace GloCurrency\Tingg\Tests\Feature\Jobs;

use Illuminate\Support\Facades\Event;
use GloCurrency\Tingg\Tests\FeatureTestCase;
use GloCurrency\Tingg\Models\Transaction;
use GloCurrency\Tingg\Jobs\SendTransactionJob;
use GloCurrency\Tingg\Exceptions\SendTransactionException;
use GloCurrency\Tingg\Events\TransactionUpdatedEvent;
use GloCurrency\Tingg\Events\TransactionCreatedEvent;
use GloCurrency\Tingg\Enums\TransactionStateCodeEnum;
use BrokeYourBike\Tingg\Enums\PaymentStatusCodeEnum;
use BrokeYourBike\Tingg\Enums\AuthCodeEnum;
use BrokeYourBike\Tingg\Client;

class SendTransactionJobTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TransactionCreatedEvent::class,
            TransactionUpdatedEvent::class,
        ]);
    }

    private function makeTransactionResponse(PaymentStatusCodeEnum $statusCode, string $remoteReference = ''): \GuzzleHttp\Psr7\Response
    {
        return new \GuzzleHttp\Psr7\Response(200, [], '{
            "authStatus": {
                "authStatusCode": 131,
                "authStatusDescription": "auth success"
            },
            "results": [{
                "statusCode": '. $statusCode->value .',
                "statusDescription": "",
                "payerTransactionID": "yourUniqueID",
                "beepTransactionID": "'. $remoteReference .'",
                "receiptNumber": ""
            }]
        }');
    }

    /**
     * @test
     * @dataProvider transactionStatesProvider
     */
    public function it_will_throw_if_state_not_LOCAL_UNPROCESSED(TransactionStateCodeEnum $stateCode, bool $shouldFail): void
    {
        /** @var Transaction */
        $targetTransaction = Transaction::factory()->create([
            'state_code' => $stateCode,
        ]);

        [$httpMock] = $this->mockApiFor(Client::class);
        $httpMock->append($this->makeTransactionResponse(PaymentStatusCodeEnum::POSTED_AND_PENDING_ACKNOWLEDGEMENT));

        if ($shouldFail) {
            $this->expectExceptionMessage("Transaction state_code `{$targetTransaction->state_code->value}` not allowed");
            $this->expectException(SendTransactionException::class);
        }

        SendTransactionJob::dispatchSync($targetTransaction);

        if (!$shouldFail) {
            $this->assertEquals(TransactionStateCodeEnum::PROCESSING, $targetTransaction->fresh()->state_code);
        }
    }

    public function transactionStatesProvider(): array
    {
        $states = collect(TransactionStateCodeEnum::cases())
            ->filter(fn($c) => !in_array($c, [
                TransactionStateCodeEnum::LOCAL_UNPROCESSED,
            ]))
            ->flatten()
            ->map(fn($c) => [$c, true])
            ->toArray();

        $states[] = [TransactionStateCodeEnum::LOCAL_UNPROCESSED, false];

        return $states;
    }

    /** @test */
    public function it_will_throw_if_auth_code_is_unexpected(): void
    {
        /** @var Transaction */
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
        ]);

        [$httpMock] = $this->mockApiFor(Client::class);
        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{
            "authStatus": {
                "authStatusCode": "not-an-auth-code",
                "authStatusDescription": ""
            },
            "results": []
        }'));

        try {
            SendTransactionJob::dispatchSync($targetTransaction);
        } catch (\Throwable $th) {
            $this->assertEquals('Unexpected ' . AuthCodeEnum::class . ': `not-an-auth-code`', $th->getMessage());
            $this->assertInstanceOf(SendTransactionException::class, $th);
        }

        /** @var Transaction */
        $targetTransaction = $targetTransaction->fresh();

        $this->assertEquals(TransactionStateCodeEnum::UNEXPECTED_AUTH_CODE, $targetTransaction->state_code);
    }

    /** @test */
    public function it_will_throw_if_error_code_is_unexpected(): void
    {
        /** @var Transaction */
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
        ]);

        [$httpMock] = $this->mockApiFor(Client::class);
        $httpMock->append(new \GuzzleHttp\Psr7\Response(200, [], '{
            "authStatus": {
                "authStatusCode": 131,
                "authStatusDescription": "auth success"
            },
            "results": [{
                "statusCode": "not a code you can expect",
                "statusDescription": "KEK",
                "payerTransactionID": "yourUniqueID",
                "beepTransactionID": -1,
                "receiptNumber": ""
            }]
        }'));

        try {
            SendTransactionJob::dispatchSync($targetTransaction);
        } catch (\Throwable $th) {
            $this->assertEquals('Unexpected ' . PaymentStatusCodeEnum::class . ': `not a code you can expect`', $th->getMessage());
            $this->assertInstanceOf(SendTransactionException::class, $th);
        }

        /** @var Transaction */
        $targetTransaction = $targetTransaction->fresh();

        $this->assertEquals(TransactionStateCodeEnum::UNEXPECTED_ERROR_CODE, $targetTransaction->state_code);
    }

    /** @test */
    public function it_can_send_transaction(): void
    {
        /** @var Transaction */
        $targetTransaction = Transaction::factory()->create([
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
            'amount' => $this->faker->randomFloat(2, 1),
        ]);

        [$httpMock] = $this->mockApiFor(Client::class);
        $httpMock->append($this->makeTransactionResponse(PaymentStatusCodeEnum::POSTED_AND_PENDING_ACKNOWLEDGEMENT, 'ref-123'));

        SendTransactionJob::dispatchSync($targetTransaction);

        /** @var Transaction */
        $targetTransaction = $targetTransaction->fresh();


        $this->assertEquals(TransactionStateCodeEnum::PROCESSING, $targetTransaction->state_code);
        $this->assertEquals(PaymentStatusCodeEnum::POSTED_AND_PENDING_ACKNOWLEDGEMENT, $targetTransaction->error_code);
        $this->assertSame('ref-123', $targetTransaction->remote_reference);
        // TODO: make more assetions
    }
}
