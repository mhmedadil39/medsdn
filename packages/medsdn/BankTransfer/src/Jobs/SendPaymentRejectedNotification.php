<?php

namespace Webkul\BankTransfer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Webkul\BankTransfer\Models\BankTransferPayment;

class SendPaymentRejectedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     *
     * @param  \Webkul\BankTransfer\Models\BankTransferPayment  $payment
     * @return void
     */
    public function __construct(
        public BankTransferPayment $payment
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            // Load order relationship if not already loaded
            $this->payment->loadMissing('order.customer');

            // Get customer email
            $customerEmail = $this->payment->order->customer_email 
                ?? $this->payment->order->customer?->email;

            if (!$customerEmail) {
                Log::warning('Cannot send payment rejected notification: customer email not found', [
                    'payment_id' => $this->payment->id,
                    'order_id' => $this->payment->order_id,
                ]);
                return;
            }

            // Send rejection email with admin note
            Mail::to($customerEmail)->send(
                new \Webkul\BankTransfer\Mail\PaymentRejected($this->payment)
            );

            // Log notification sent
            Log::info('Payment rejected notification sent to customer', [
                'payment_id' => $this->payment->id,
                'order_id' => $this->payment->order_id,
                'admin_note' => $this->payment->admin_note,
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the job
            Log::error('Failed to send payment rejected notification', [
                'payment_id' => $this->payment->id,
                'order_id' => $this->payment->order_id,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to allow retry
            throw $e;
        }
    }
}
