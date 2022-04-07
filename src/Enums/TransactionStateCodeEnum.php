<?php

namespace GloCurrency\Tingg\Enums;

use GloCurrency\MiddlewareBlocks\Enums\ProcessingItemStateCodeEnum as MProcessingItemStateCodeEnum;
use BrokeYourBike\Tingg\Enums\PaymentStatusCodeEnum;
use BrokeYourBike\Tingg\Enums\AuthCodeEnum;

enum TransactionStateCodeEnum: string
{
    case LOCAL_UNPROCESSED = 'local_unprocessed';
    case LOCAL_EXCEPTION = 'local_exception';
    case STATE_NOT_ALLOWED = 'state_not_allowed';
    case API_REQUEST_EXCEPTION = 'api_request_exception';
    case NO_ERROR_CODE_PROPERTY = 'no_error_code_property';
    case UNEXPECTED_ERROR_CODE = 'unexpected_error_code';
    case NO_AUTH_CODE_PROPERTY = 'no_transaction_auth_code_property';
    case UNEXPECTED_AUTH_CODE = 'unexpected_transaction_auth_code';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case FAILED = 'failed';
    case API_ERROR = 'api_error';
    case API_TIMEOUT = 'api_timeout';
    case INVALID_AMOUNT = 'invalid_amount';
    case DUPLICATE_TRANSACTION = 'duplicate_transaction';

    public static function makeFromAuthCode(AuthCodeEnum $authCode): self
    {
        return match ($authCode) {
            AuthCodeEnum::AUTH_SUCCESS => TransactionStateCodeEnum::PROCESSING,
            AuthCodeEnum::AUTH_FAILED => TransactionStateCodeEnum::API_ERROR,
            AuthCodeEnum::GENERIC_FAILURE => TransactionStateCodeEnum::API_ERROR,
        };
    }

    public static function makeFromPaymentStatusCode(PaymentStatusCodeEnum $statusCode): self
    {
        return match ($statusCode) {
            PaymentStatusCodeEnum::GENERIC_EXCEPTION => TransactionStateCodeEnum::API_ERROR,
            PaymentStatusCodeEnum::INACTIVE_SERVICE => TransactionStateCodeEnum::API_ERROR,
            PaymentStatusCodeEnum::CUSTOMER_MSISDN_MISSING => TransactionStateCodeEnum::API_ERROR,
            PaymentStatusCodeEnum::INVALID_CUSTOMER_MSISDN => TransactionStateCodeEnum::API_ERROR,
            PaymentStatusCodeEnum::INVALID_INVOICE_AMOUNT => TransactionStateCodeEnum::INVALID_AMOUNT,
            PaymentStatusCodeEnum::INVALID_CURRENCY_CODE => TransactionStateCodeEnum::API_ERROR,
            PaymentStatusCodeEnum::ACCOUNT_NUMBER_NOT_SPECIFIED => TransactionStateCodeEnum::API_ERROR,
            PaymentStatusCodeEnum::POSTED_AND_PENDING_ACKNOWLEDGEMENT => TransactionStateCodeEnum::PROCESSING,
            PaymentStatusCodeEnum::INVOICE_DOES_NOT_EXIST => TransactionStateCodeEnum::API_ERROR,
            PaymentStatusCodeEnum::INVALID_SERVICEID => TransactionStateCodeEnum::API_ERROR,
            PaymentStatusCodeEnum::GENERIC_FAILURE => TransactionStateCodeEnum::API_ERROR,
            PaymentStatusCodeEnum::PAYMENT_REJECTED => TransactionStateCodeEnum::FAILED,
            PaymentStatusCodeEnum::PAYMENT_ACCEPTED => TransactionStateCodeEnum::PAID,
            PaymentStatusCodeEnum::PAYMENT_MANUALLY_REJECTED => TransactionStateCodeEnum::FAILED,
            PaymentStatusCodeEnum::PAYMENT_MANUALLY_ACCEPTED => TransactionStateCodeEnum::PAID,
            PaymentStatusCodeEnum::PAYMENT_ESCALATED => TransactionStateCodeEnum::PROCESSING,
            PaymentStatusCodeEnum::AMOUNT_SPECIFIED_IS_GREATER_THAN_MAXIMUM_ALLOWED => TransactionStateCodeEnum::INVALID_AMOUNT,
            PaymentStatusCodeEnum::AMOUNT_SPECIFIED_IS_LESS_THAN_MINIMUM_ALLOWED => TransactionStateCodeEnum::INVALID_AMOUNT,
            PaymentStatusCodeEnum::DUPLICATE_PAYMENT_FOUND => TransactionStateCodeEnum::DUPLICATE_TRANSACTION,
        };
    }

    /**
     * Get the ProcessingItem state based on Transaction state.
     */
    public function getProcessingItemStateCode(): MProcessingItemStateCodeEnum
    {
        return match ($this) {
            self::LOCAL_UNPROCESSED => MProcessingItemStateCodeEnum::PENDING,
            self::LOCAL_EXCEPTION => MProcessingItemStateCodeEnum::MANUAL_RECONCILIATION_REQUIRED,
            self::STATE_NOT_ALLOWED => MProcessingItemStateCodeEnum::EXCEPTION,
            self::API_REQUEST_EXCEPTION => MProcessingItemStateCodeEnum::EXCEPTION,
            self::NO_ERROR_CODE_PROPERTY => MProcessingItemStateCodeEnum::EXCEPTION,
            self::UNEXPECTED_ERROR_CODE => MProcessingItemStateCodeEnum::EXCEPTION,
            self::NO_AUTH_CODE_PROPERTY => MProcessingItemStateCodeEnum::EXCEPTION,
            self::UNEXPECTED_AUTH_CODE => MProcessingItemStateCodeEnum::EXCEPTION,
            self::PROCESSING => MProcessingItemStateCodeEnum::PROVIDER_PENDING,
            self::PAID => MProcessingItemStateCodeEnum::PROCESSED,
            self::FAILED => MProcessingItemStateCodeEnum::MANUAL_RECONCILIATION_REQUIRED,
            self::API_ERROR => MProcessingItemStateCodeEnum::PROVIDER_NOT_ACCEPTING_TRANSACTIONS,
            self::API_TIMEOUT => MProcessingItemStateCodeEnum::PROVIDER_TIMEOUT,
            self::INVALID_AMOUNT => MProcessingItemStateCodeEnum::TRANSACTION_AMOUNT_INVALID,
            self::DUPLICATE_TRANSACTION => MProcessingItemStateCodeEnum::DUPLICATE_TARGET_TRANSACTION,
        };
    }
}
