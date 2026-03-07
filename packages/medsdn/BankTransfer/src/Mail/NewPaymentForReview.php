<?php

namespace Webkul\BankTransfer\Mail;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Webkul\BankTransfer\Models\BankTransferPayment;

class NewPaymentForReview extends Mailable
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

        return new Envelope(
            subject: trans('banktransfer::app.emails.admin.new-payment.subject', [
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
            view: 'banktransfer::emails.admin.new-payment',
        );
    }
}
