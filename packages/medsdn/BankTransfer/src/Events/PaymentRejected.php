<?php

namespace Webkul\BankTransfer\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webkul\BankTransfer\Models\BankTransferPayment;
use Webkul\User\Models\Admin;

class PaymentRejected
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  \Webkul\BankTransfer\Models\BankTransferPayment  $payment
     * @param  \Webkul\User\Models\Admin  $admin
     * @param  string  $note
     * @return void
     */
    public function __construct(
        public readonly BankTransferPayment $payment,
        public readonly Admin $admin,
        public readonly string $note
    ) {}
}
