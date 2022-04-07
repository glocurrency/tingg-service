<?php

namespace GloCurrency\Tingg\Tests\Feature\Commands;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use GloCurrency\Tingg\Tests\FeatureTestCase;
use GloCurrency\Tingg\Models\Transaction;
use GloCurrency\Tingg\Jobs\FetchTransactionUpdateJob;
use GloCurrency\Tingg\Events\TransactionUpdatedEvent;
use GloCurrency\Tingg\Events\TransactionCreatedEvent;
use GloCurrency\Tingg\Enums\TransactionStateCodeEnum;

class FetchTransactionsUpdateCommandTest extends FeatureTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([
            TransactionCreatedEvent::class,
            TransactionUpdatedEvent::class,
        ]);
    }

    /** @test */
    public function exit_if_no_transactions_found(): void
    {
        Queue::fake();

        $transaction = Transaction::factory()->create();
        $transaction->delete();

        $this->artisan('tingg:fetch-update')
            ->expectsOutput('You do not have any unfinished Tingg/Transaction')
            ->assertExitCode(0);

        Queue::assertNotPushed(FetchTransactionUpdateJob::class);
    }

    /**
     * @test
     * @dataProvider transactionStateCodeProvider
     * */
    public function dispatch_job_for_transactions_with_state_code(TransactionStateCodeEnum $stateCode, int $dispatchCount): void
    {
        Bus::fake([FetchTransactionUpdateJob::class]);

        Transaction::factory()->create([
            'state_code' => $stateCode,
        ]);

        $this->artisan('tingg:fetch-update')
            ->assertExitCode(0);

        Bus::assertDispatchedTimes(FetchTransactionUpdateJob::class, $dispatchCount);
    }

    public function transactionStateCodeProvider(): array
    {
        $states = collect(TransactionStateCodeEnum::cases())
            ->filter(fn($c) => !in_array($c, [
                TransactionStateCodeEnum::PROCESSING,
            ]))
            ->flatten()
            ->map(fn($c) => [$c, 0])
            ->toArray();

        $states[] = [TransactionStateCodeEnum::PROCESSING, 1];

        return $states;
    }
}
