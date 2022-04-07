<?php

namespace GloCurrency\Tingg\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use GloCurrency\MiddlewareBlocks\Contracts\ModelEventInterface as MModelEventInterface;
use GloCurrency\Tingg\Models\Transaction;

class TransactionCreatedEvent implements MModelEventInterface
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Transaction $transaction;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function getItem(): Transaction
    {
        return $this->transaction;
    }
}
