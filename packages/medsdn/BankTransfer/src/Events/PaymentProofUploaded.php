<?php

namespace Webkul\BankTransfer\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webkul\BankTransfer\Models\BankTransferPayment;
use Webkul\Sales\Models\Order;

class PaymentProofUploaded
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  \Webkul\BankTransfer\Models\BankTransferPayment  $payment
     * @param  \Webkul\Sales\Models\Order  $order
     * @return void
     */
    public function __construct(
        public readonly BankTransferPayment $payment,
        public readonly Order $order
    ) {}
}
