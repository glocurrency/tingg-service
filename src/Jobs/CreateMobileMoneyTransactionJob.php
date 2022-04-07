<?php

namespace GloCurrency\Tingg\Jobs;

use Money\Formatter\DecimalMoneyFormatter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Bus\Queueable;
use GloCurrency\Tingg\Tingg;
use GloCurrency\Tingg\Models\Transaction;
use GloCurrency\Tingg\Models\MobileMoneyProvider;
use GloCurrency\Tingg\Exceptions\CreateTransactionException;
use GloCurrency\Tingg\Enums\TransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Enums\TransactionTypeEnum as MTransactionTypeEnum;
use GloCurrency\MiddlewareBlocks\Enums\TransactionStateCodeEnum as MTransactionStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Enums\QueueTypeEnum as MQueueTypeEnum;
use GloCurrency\MiddlewareBlocks\Enums\ProcessingItemStateCodeEnum as MProcessingItemStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Contracts\ProcessingItemInterface as MProcessingItemInterface;
use GloCurrency\MiddlewareBlocks\Contracts\MobileMoneyProviderInterface as MMobileMoneyProviderInterface;

class CreateMobileMoneyTransactionJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
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

    private MProcessingItemInterface $processingItem;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MProcessingItemInterface $processingItem)
    {
        $this->processingItem = $processingItem;
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
        return $this->processingItem->getId();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $transaction = $this->processingItem->getTransaction();

        if (!$transaction) {
            throw CreateTransactionException::noTransaction($this->processingItem);
        }

        if (MTransactionTypeEnum::MOBILE !== $transaction->getType()) {
            throw CreateTransactionException::typeNotAllowed($transaction);
        }

        if (MTransactionStateCodeEnum::PROCESSING !== $transaction->getStateCode()) {
            throw CreateTransactionException::stateNotAllowed($transaction);
        }

        /** @var Transaction|null $targetTransaction */
        $targetTransaction = Transaction::firstWhere('transaction_id', $transaction->getId());

        if ($targetTransaction) {
            throw CreateTransactionException::duplicateTargetTransaction($targetTransaction);
        }

        $transactionSender = $transaction->getSender();

        if (!$transactionSender) {
            throw CreateTransactionException::noTransactionSender($transaction);
        }

        $transactionRecipient = $transaction->getRecipient();

        if (!$transactionRecipient) {
            throw CreateTransactionException::noTransactionRecipient($transaction);
        }

        if (!$transactionRecipient->getMobileProvider()) {
            throw CreateTransactionException::emptyMobileProvider($transactionRecipient);
        }

        if (!$transactionRecipient->getPhoneNumber()) {
            throw CreateTransactionException::emptyPhoneNumber($transactionRecipient);
        }

        $mobileMoneyProvider = (Tingg::$mobileMoneyProviderModel)::firstWhere([
            'code' => $transactionRecipient->getMobileProvider(),
            'country_code' => $transactionRecipient->getCountryCode(),
        ]);

        if (!$mobileMoneyProvider instanceof MMobileMoneyProviderInterface) {
            throw CreateTransactionException::noMobileProvider($transactionRecipient);
        }

        $targetMobileMoneyProvider = MobileMoneyProvider::firstWhere([
            'mobile_money_provider_id' => $mobileMoneyProvider->getId(),
        ]);

        if (!$targetMobileMoneyProvider instanceof MobileMoneyProvider) {
            throw CreateTransactionException::noTargetMobileProvider($mobileMoneyProvider);
        }

        /** @var DecimalMoneyFormatter $moneyFormatter */
        $moneyFormatter = App::make(DecimalMoneyFormatter::class);

        Transaction::create([
            'transaction_id' => $transaction->getId(),
            'processing_item_id' => $this->processingItem->getId(),
            'state_code' => TransactionStateCodeEnum::LOCAL_UNPROCESSED,
            'reference' => $transaction->getReferenceForHumans(),
            'country_code' => $transactionRecipient->getCountryCode(),
            'currency_code' => $transaction->getOutputAmount()->getCurrency()->getCode(),
            'amount' => $moneyFormatter->format($transaction->getOutputAmount()),
            'service_code' => $targetMobileMoneyProvider->code,
            'sender_name' => (string) Config::get('services.tingg.sender_name'),
            'sender_phone_number' => (string) Config::get('services.tingg.sender_phone_number'),
            'recipient_phone_number' => $transactionRecipient->getPhoneNumber(),
        ]);
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

        if ($exception instanceof CreateTransactionException) {
            $this->processingItem->updateStateCode($exception->getStateCode(), $exception->getStateCodeReason());
            return;
        }

        $this->processingItem->updateStateCode(MProcessingItemStateCodeEnum::EXCEPTION, $exception->getMessage());
    }
}
