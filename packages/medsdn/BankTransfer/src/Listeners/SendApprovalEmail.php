<?php

namespace Webkul\BankTransfer\Listeners;

use Illuminate\Support\Facades\Log;
use Webkul\BankTransfer\Events\PaymentApproved;
use Webkul\BankTransfer\Jobs\SendPaymentApprovedNotification;

class SendApprovalEmail
{
    /**
     * Handle the event.
     *
     * @param  \Webkul\BankTransfer\Events\PaymentApproved  $event
     * @return void
     */
    public function handle(PaymentApproved $event): void
    {
        try {
            // Dispatch the queue job to send customer approval notification
            SendPaymentApprovedNotification::dispatch($event->payment);

            // Log listener execution
            Log::info('SendApprovalEmail dispatched job for approved payment', [
                'payment_id' => $event->payment->id,
                'order_id' => $event->payment->order_id,
                'admin_id' => $event->admin->id,
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the workflow
            Log::error('SendApprovalEmail failed to dispatch job', [
                'payment_id' => $event->payment->id,
                'order_id' => $event->payment->order_id,
                'admin_id' => $event->admin->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
