<?php

namespace GloCurrency\Tingg\Exceptions;

use GloCurrency\Tingg\Tingg;
use GloCurrency\Tingg\Models\Transaction;
use GloCurrency\Tingg\Models\MobileMoneyProvider;
use GloCurrency\MiddlewareBlocks\Enums\ProcessingItemStateCodeEnum as MProcessingItemStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Contracts\TransactionInterface as MTransactionInterface;
use GloCurrency\MiddlewareBlocks\Contracts\RecipientInterface as MRecipientInterface;
use GloCurrency\MiddlewareBlocks\Contracts\ProcessingItemInterface as MProcessingItemInterface;
use GloCurrency\MiddlewareBlocks\Contracts\MobileMoneyProviderInterface as MMobileMoneyProviderInterface;

final class CreateTransactionException extends \RuntimeException
{
    private MProcessingItemStateCodeEnum $stateCode;
    private string $stateCodeReason;

    public function __construct(MProcessingItemStateCodeEnum $stateCode, string $stateCodeReason, ?\Throwable $previous = null)
    {
        $this->stateCode = $stateCode;
        $this->stateCodeReason = $stateCodeReason;

        parent::__construct($stateCodeReason, 0, $previous);
    }

    public function getStateCode(): MProcessingItemStateCodeEnum
    {
        return $this->stateCode;
    }

    public function getStateCodeReason(): string
    {
        return $this->stateCodeReason;
    }

    public static function noTransaction(MProcessingItemInterface $processingItem): self
    {
        $className = $processingItem::class;
        $message = "{$className} `{$processingItem->getId()}` transaction not found";
        return new static(MProcessingItemStateCodeEnum::NO_TRANSACTION, $message);
    }

    public static function noTransactionSender(MTransactionInterface $transaction): self
    {
        $className = $transaction::class;
        $message = "{$className} `{$transaction->getId()}` sender not found";
        return new static(MProcessingItemStateCodeEnum::NO_TRANSACTION_SENDER, $message);
    }

    public static function noTransactionRecipient(MTransactionInterface $transaction): self
    {
        $className = $transaction::class;
        $message = "{$className} `{$transaction->getId()}` recipient not found";
        return new static(MProcessingItemStateCodeEnum::NO_TRANSACTION_RECIPIENT, $message);
    }

    public static function typeNotAllowed(MTransactionInterface $transaction): self
    {
        $className = $transaction::class;
        $message = "{$className} type `{$transaction->getType()->value}` not allowed";
        return new static(MProcessingItemStateCodeEnum::TRANSACTION_TYPE_NOT_ALLOWED, $message);
    }

    public static function stateNotAllowed(MTransactionInterface $transaction): self
    {
        $className = $transaction::class;
        $message = "{$className} state_code `{$transaction->getStateCode()->value}` not allowed";
        return new static(MProcessingItemStateCodeEnum::TRANSACTION_STATE_NOT_ALLOWED, $message);
    }

    public static function duplicateTargetTransaction(Transaction $fidelityTransaction): self
    {
        $className = $fidelityTransaction::class;
        $message = "{$className} cannot be created twice, `{$fidelityTransaction->id}`";
        return new static(MProcessingItemStateCodeEnum::DUPLICATE_TARGET_TRANSACTION, $message);
    }

    public static function emptyMobileProvider(MRecipientInterface $transactionRecipient): self
    {
        $className = $transactionRecipient::class;
        $message = "{$className} `{$transactionRecipient->getId()}` has no `mobile_provider`";
        return new static(MProcessingItemStateCodeEnum::NO_TRANSACTION_RECIPIENT_MOBILE_PROVIDER, $message);
    }

    public static function emptyPhoneNumber(MRecipientInterface $transactionRecipient): self
    {
        $className = $transactionRecipient::class;
        $message = "{$className} `{$transactionRecipient->getId()}` has no `phone_number`";
        return new static(MProcessingItemStateCodeEnum::NO_TRANSACTION_RECIPIENT_PHONE_NUMBER, $message);
    }

    public static function noMobileProvider(MRecipientInterface $transactionRecipient): self
    {
        $className = $transactionRecipient::class;
        $providerClassName = (Tingg::$mobileMoneyProviderModel);
        return new static(MProcessingItemStateCodeEnum::NO_TRANSACTION_RECIPIENT_MOBILE_PROVIDER, "No {$providerClassName} for {$className} `{$transactionRecipient->getId()}`");
    }

    public static function noTargetMobileProvider(MMobileMoneyProviderInterface $mobileProvider): self
    {
        $className = $mobileProvider::class;
        $providerClassName = MobileMoneyProvider::class;
        return new static(MProcessingItemStateCodeEnum::NO_TARGET_MOBILE_PROVIDER, "No {$providerClassName} for {$className} `{$mobileProvider->getId()}`");
    }
}
