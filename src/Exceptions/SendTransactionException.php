<?php

namespace GloCurrency\Tingg\Exceptions;

use GloCurrency\Tingg\Models\Transaction;
use GloCurrency\Tingg\Enums\TransactionStateCodeEnum;
use BrokeYourBike\Tingg\Enums\PaymentStatusCodeEnum;
use BrokeYourBike\Tingg\Enums\AuthCodeEnum;
use BrokeYourBike\Tingg\Client;
use BrokeYourBike\DataTransferObject\JsonResponse;

final class SendTransactionException extends \RuntimeException
{
    private TransactionStateCodeEnum $stateCode;
    private string $stateCodeReason;

    public function __construct(TransactionStateCodeEnum $stateCode, string $stateCodeReason, ?\Throwable $previous = null)
    {
        $this->stateCode = $stateCode;
        $this->stateCodeReason = $stateCodeReason;

        parent::__construct($stateCodeReason, 0, $previous);
    }

    public function getStateCode(): TransactionStateCodeEnum
    {
        return $this->stateCode;
    }

    public function getStateCodeReason(): string
    {
        return $this->stateCodeReason;
    }

    public static function stateNotAllowed(Transaction $transaction): self
    {
        $className = $transaction::class;
        $message = "{$className} state_code `{$transaction->state_code->value}` not allowed";
        return new static(TransactionStateCodeEnum::STATE_NOT_ALLOWED, $message);
    }

    public static function apiRequestException(\Throwable $e): self
    {
        $className = Client::class;
        $message = "Exception during {$className} request with message: `{$e->getMessage()}`";
        return new static(TransactionStateCodeEnum::API_REQUEST_EXCEPTION, $message);
    }

    public static function unexpectedAuthCode(string $code): self
    {
        $className = AuthCodeEnum::class;
        $message = "Unexpected {$className}: `{$code}`";
        return new static(TransactionStateCodeEnum::UNEXPECTED_AUTH_CODE, $message);
    }

    public static function noErrorCode(JsonResponse $response): self
    {
        $className = PaymentStatusCodeEnum::class;
        $message = "No {$className} in json `{$response->getRawResponse()->getBody()}`";
        return new static(TransactionStateCodeEnum::NO_ERROR_CODE_PROPERTY, $message);
    }

    public static function unexpectedErrorCode(string $code): self
    {
        $className = PaymentStatusCodeEnum::class;
        $message = "Unexpected {$className}: `{$code}`";
        return new static(TransactionStateCodeEnum::UNEXPECTED_ERROR_CODE, $message);
    }
}
