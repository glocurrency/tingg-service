<?php

namespace GloCurrency\Tingg\Jobs;

use Illuminate\Support\Facades\App;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Bus\Queueable;
use GloCurrency\Tingg\Models\Transaction;
use GloCurrency\Tingg\Exceptions\FetchTransactionUpdateException;
use GloCurrency\Tingg\Enums\TransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Enums\QueueTypeEnum as MQueueTypeEnum;
use BrokeYourBike\Tingg\Models\PaymentResult;
use BrokeYourBike\Tingg\Enums\PaymentStatusCodeEnum;
use BrokeYourBike\Tingg\Enums\AuthCodeEnum;
use BrokeYourBike\Tingg\Client;

class FetchTransactionUpdateJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    private Transaction $targetTransaction;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Transaction $targetTransaction)
    {
        $this->targetTransaction = $targetTransaction;
        $this->afterCommit();
        $this->onQueue(MQueueTypeEnum::SERVICES->value);
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->targetTransaction->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (TransactionStateCodeEnum::PROCESSING !== $this->targetTransaction->state_code) {
            throw FetchTransactionUpdateException::stateNotAllowed($this->targetTransaction);
        }

        if (empty($this->targetTransaction->batch_reference)) {
            throw FetchTransactionUpdateException::emptyRemoteReference($this->targetTransaction);
        }

        try {
            /** @var Client */
            $api = App::make(Client::class);
            $response = $api->queryPaymentStatus($this->targetTransaction);
        } catch (\Throwable $e) {
            report($e);
            throw FetchTransactionUpdateException::apiRequestException($e);
        }

        $authCode = AuthCodeEnum::tryFrom($response->authStatus->authStatusCode);

        if (!$authCode) {
            throw FetchTransactionUpdateException::unexpectedAuthCode($response->authStatus->authStatusCode);
        }

        [$paymentResult] = $response->results;

        if (!$paymentResult instanceof PaymentResult) {
            throw FetchTransactionUpdateException::noErrorCode($response);
        }

        $errorCode = PaymentStatusCodeEnum::tryFrom($paymentResult->statusCode);

        if (!$errorCode) {
            throw FetchTransactionUpdateException::unexpectedErrorCode($paymentResult->statusCode);
        }

        $this->targetTransaction->state_code = TransactionStateCodeEnum::makeFromPaymentStatusCode($errorCode);
        $this->targetTransaction->save();
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        report($exception);
    }
}
