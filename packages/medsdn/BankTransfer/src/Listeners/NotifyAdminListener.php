<?php

namespace Webkul\BankTransfer\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\BankTransfer\Events\PaymentProofUploaded;
use Webkul\BankTransfer\Jobs\NotifyAdminNewPayment;

class NotifyAdminListener
{
    /**
     * Handle the event.
     *
     * @param  \Webkul\BankTransfer\Events\PaymentProofUploaded  $event
     * @return void
     */
    public function handle(PaymentProofUploaded $event): void
    {
        try {
            // Dispatch the queue job to send admin notification
            NotifyAdminNewPayment::dispatch($event->payment);

            // Log listener execution
            Log::info('NotifyAdminListener dispatched job for new payment', [
                'payment_id' => $event->payment->id,
                'order_id' => $event->order->id,
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the workflow
            Log::error('NotifyAdminListener failed to dispatch job', [
                'payment_id' => $event->payment->id,
                'order_id' => $event->order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
