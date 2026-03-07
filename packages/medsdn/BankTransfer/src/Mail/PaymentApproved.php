<?php

namespace Webkul\BankTransfer\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Webkul\BankTransfer\Models\BankTransferPayment;

class PaymentApproved extends Mailable
{
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  \Webkul\BankTransfer\Models\BankTransferPayment  $payment
     * @return void
     */
    public function __construct(
        public BankTransferPayment $payment
    ) {}

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        // Load order relationship if not already loaded
        $this->payment->loadMissing('order.customer');

        $orderNumber = $this->payment->order->increment_id ?? '#' . $this->payment->order_id;
        $customerEmail = $this->payment->order->customer_email 
            ?? $this->payment->order->customer?->email;
        $customerName = $this->payment->order->customer_full_name 
            ?? $this->payment->order->customer?->name 
            ?? 'Customer';

        // Ensure we have a valid email address
        if (empty($customerEmail)) {
            \Log::warning('PaymentApproved email: Customer email is missing', [
                'payment_id' => $this->payment->id,
                'order_id' => $this->payment->order_id,
            ]);
            
            // Don't send email when customer email is missing
            throw new \Exception('Customer email is required for payment approval notification');
        }

        return new Envelope(
            to: [
                new Address(
                    $customerEmail,
                    $customerName
                ),
            ],
            subject: trans('banktransfer::app.emails.customer.payment-approved.subject', [
                'order_number' => $orderNumber,
            ]),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'banktransfer::emails.customer.payment-approved',
        );
    }
}
