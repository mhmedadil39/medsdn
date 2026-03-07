@component('shop::emails.layout')
    <div style="margin-bottom: 34px;">
        <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;">
            @lang('shop::app.emails.dear', ['customer_name' => $payment->order->customer_full_name ?? $payment->order->customer?->name ?? 'Customer']),👋
        </p>

        <p style="font-weight: bold;font-size: 20px;color: #121A26;line-height: 24px;margin-top: 20px;">
            @lang('banktransfer::app.emails.customer.payment-rejected.greeting')
        </p>

        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            @lang('banktransfer::app.emails.customer.payment-rejected.description')
        </p>
    </div>

    <div style="font-size: 20px;font-weight: 600;color: #121A26;margin-bottom: 20px;">
        @lang('banktransfer::app.emails.customer.payment-rejected.order-details')
    </div>

    <div style="background: #FEF2F2;padding: 20px;border-radius: 8px;border-left: 4px solid #EF4444;margin-bottom: 30px;">
        <div style="display: grid;gap: 15px;">
            <div style="display: grid;gap: 5px;">
                <span style="font-size: 14px;color: #991B1B;font-weight: 500;">
                    @lang('banktransfer::app.emails.customer.payment-rejected.order-number')
                </span>
                <span style="font-size: 16px;color: #121A26;font-weight: 600;">
                    <a href="{{ route('shop.customers.account.orders.view', $payment->order->id) }}" style="color: #2969FF;text-decoration: none;">
                        #{{ $payment->order->increment_id ?? $payment->order_id }}
                    </a>
                </span>
            </div>

            <div style="display: grid;gap: 5px;">
                <span style="font-size: 14px;color: #991B1B;font-weight: 500;">
                    @lang('banktransfer::app.emails.customer.payment-rejected.order-total')
                </span>
                <span style="font-size: 18px;color: #121A26;font-weight: 700;">
                    {{ $payment->order ? core()->formatPrice($payment->order->grand_total, $payment->order->order_currency_code) : core()->formatPrice(0) }}
                </span>
            </div>

            <div style="display: grid;gap: 5px;">
                <span style="font-size: 14px;color: #991B1B;font-weight: 500;">
                    @lang('banktransfer::app.emails.customer.payment-rejected.rejected-date')
                </span>
                <span style="font-size: 16px;color: #121A26;">
                    {{ $payment->reviewed_at ? core()->formatDate($payment->reviewed_at, 'Y-m-d H:i:s') : '—' }}
                </span>
            </div>
        </div>
    </div>

    @if ($payment->admin_note)
        <div style="background: #FFFBEB;padding: 20px;border-radius: 8px;border-left: 4px solid #F59E0B;margin-bottom: 30px;">
            <div style="font-size: 16px;font-weight: 600;color: #92400E;margin-bottom: 10px;">
                @lang('banktransfer::app.emails.customer.payment-rejected.rejection-reason')
            </div>
            <p style="font-size: 16px;color: #78350F;line-height: 24px;margin: 0;">
                {{ $payment->admin_note }}
            </p>
        </div>
    @endif

    <div style="background: #F9FAFB;padding: 20px;border-radius: 8px;margin-bottom: 30px;">
        <p style="font-size: 16px;color: #384860;line-height: 24px;margin: 0 0 15px 0;">
            @lang('banktransfer::app.emails.customer.payment-rejected.next-steps')
        </p>
        <ul style="font-size: 16px;color: #384860;line-height: 24px;margin: 0;padding-left: 20px;">
            <li>@lang('banktransfer::app.emails.customer.payment-rejected.step-1')</li>
            <li>@lang('banktransfer::app.emails.customer.payment-rejected.step-2')</li>
            <li>@lang('banktransfer::app.emails.customer.payment-rejected.step-3')</li>
        </ul>
    </div>

    <div style="margin-bottom: 40px;">
        <a
            href="{{ route('shop.contact.index') }}"
            style="padding: 16px 45px;justify-content: center;align-items: center;gap: 10px;border-radius: 2px;background: #060C3B;color: #FFFFFF;text-decoration: none;text-transform: uppercase;font-weight: 700;display: inline-block;"
        >
            @lang('banktransfer::app.emails.customer.payment-rejected.contact-support-button')
        </a>
    </div>

    <p style="font-size: 14px;color: #6B7280;line-height: 20px;">
        @lang('banktransfer::app.emails.customer.payment-rejected.footer-note')
    </p>
@endcomponent
