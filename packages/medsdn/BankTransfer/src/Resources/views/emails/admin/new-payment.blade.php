@component('shop::emails.layout')
    <div style="margin-bottom: 34px;">
        <p style="font-weight: bold;font-size: 20px;color: #121A26;line-height: 24px;margin-bottom: 24px">
            @lang('banktransfer::app.emails.admin.new-payment.greeting')
        </p>

        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            @lang('banktransfer::app.emails.admin.new-payment.description')
        </p>
    </div>

    <div style="font-size: 20px;font-weight: 600;color: #121A26;margin-bottom: 20px;">
        @lang('banktransfer::app.emails.admin.new-payment.order-details')
    </div>

    <div style="background: #F9FAFB;padding: 20px;border-radius: 8px;margin-bottom: 30px;">
        <div style="display: grid;gap: 15px;">
            <div style="display: grid;gap: 5px;">
                <span style="font-size: 14px;color: #6B7280;font-weight: 500;">
                    @lang('banktransfer::app.emails.admin.new-payment.order-number')
                </span>
                <span style="font-size: 16px;color: #121A26;font-weight: 600;">
                    #{{ $payment->order->increment_id ?? $payment->order_id }}
                </span>
            </div>

            <div style="display: grid;gap: 5px;">
                <span style="font-size: 14px;color: #6B7280;font-weight: 500;">
                    @lang('banktransfer::app.emails.admin.new-payment.customer-name')
                </span>
                <span style="font-size: 16px;color: #121A26;">
                    {{ $payment->order->customer_full_name ?? $payment->order->customer?->name ?? 'Guest' }}
                </span>
            </div>

            <div style="display: grid;gap: 5px;">
                <span style="font-size: 14px;color: #6B7280;font-weight: 500;">
                    @lang('banktransfer::app.emails.admin.new-payment.customer-email')
                </span>
                <span style="font-size: 16px;color: #121A26;">
                    {{ $payment->order->customer_email ?? $payment->order->customer?->email ?? 'N/A' }}
                </span>
            </div>

            <div style="display: grid;gap: 5px;">
                <span style="font-size: 14px;color: #6B7280;font-weight: 500;">
                    @lang('banktransfer::app.emails.admin.new-payment.order-total')
                </span>
                <span style="font-size: 18px;color: #121A26;font-weight: 700;">
                    {{ core()->formatPrice(optional($payment->order)->grand_total ?? 0, optional($payment->order)->order_currency_code ?? '') }}
                </span>
            </div>

            @if ($payment->transaction_reference)
                <div style="display: grid;gap: 5px;">
                    <span style="font-size: 14px;color: #6B7280;font-weight: 500;">
                        @lang('banktransfer::app.emails.admin.new-payment.transaction-reference')
                    </span>
                    <span style="font-size: 16px;color: #121A26;">
                        {{ $payment->transaction_reference }}
                    </span>
                </div>
            @endif

            <div style="display: grid;gap: 5px;">
                <span style="font-size: 14px;color: #6B7280;font-weight: 500;">
                    @lang('banktransfer::app.emails.admin.new-payment.upload-date')
                </span>
                <span style="font-size: 16px;color: #121A26;">
                    {{ core()->formatDate($payment->created_at, 'Y-m-d H:i:s') }}
                </span>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 40px;">
        <a
            href="{{ route('admin.sales.bank-transfers.view', $payment->id) }}"
            style="padding: 16px 45px;justify-content: center;align-items: center;gap: 10px;border-radius: 2px;background: #060C3B;color: #FFFFFF;text-decoration: none;text-transform: uppercase;font-weight: 700;display: inline-block;"
        >
            @lang('banktransfer::app.emails.admin.new-payment.review-button')
        </a>
    </div>

    <p style="font-size: 14px;color: #6B7280;line-height: 20px;">
        @lang('banktransfer::app.emails.admin.new-payment.footer-note')
    </p>
@endcomponent
