<?php

namespace Webkul\BankTransfer\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\BankTransfer\Events\PaymentRejected;
use Webkul\BankTransfer\Jobs\SendPaymentRejectedNotification;

class SendRejectionEmail
{
    /**
     * Handle the event.
     *
     * @param  \Webkul\BankTransfer\Events\PaymentRejected  $event
     * @return void
     */
    public function handle(PaymentRejected $event): void
    {
        try {
            // Dispatch the queue job to send customer rejection notification
            SendPaymentRejectedNotification::dispatch($event->payment);

            // Log listener execution
            Log::info('SendRejectionEmail dispatched job for rejected payment', [
                'payment_id' => $event->payment->id,
                'order_id' => $event->payment->order_id,
                'admin_id' => $event->admin->id,
                'admin_note' => $event->note,
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the workflow
            Log::error('SendRejectionEmail failed to dispatch job', [
                'payment_id' => $event->payment->id,
                'order_id' => $event->payment->order_id,
                'admin_id' => $event->admin->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
