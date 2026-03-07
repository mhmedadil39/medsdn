@component('shop::emails.layout')
    <div style="margin-bottom: 34px;">
        <p style="font-size: 16px;color: #5E5E5E;line-height: 24px;">
            @lang('shop::app.emails.dear', ['customer_name' => $payment->order->customer_full_name ?? $payment->order->customer?->name ?? 'Customer']),👋
        </p>

        <p style="font-weight: bold;font-size: 20px;color: #121A26;line-height: 24px;margin-top: 20px;">
            @lang('banktransfer::app.emails.customer.payment-approved.greeting')
        </p>

        <p style="font-size: 16px;color: #384860;line-height: 24px;">
            @lang('banktransfer::app.emails.customer.payment-approved.description')
        </p>
    </div>

    <div style="font-size: 20px;font-weight: 600;color: #121A26;margin-bottom: 20px;">
        @lang('banktransfer::app.emails.customer.payment-approved.order-details')
    </div>

    <div style="background: #F0FDF4;padding: 20px;border-radius: 8px;border-left: 4px solid #10B981;margin-bottom: 30px;">
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse: collapse;">
            <tr>
                <td style="padding-bottom: 15px;">
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td style="padding-bottom: 5px;">
                                <span style="font-size: 14px;color: #065F46;font-weight: 500;">
                                    @lang('banktransfer::app.emails.customer.payment-approved.order-number')
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span style="font-size: 16px;color: #121A26;font-weight: 600;">
                                    <a href="{{ route('shop.customers.account.orders.view', $payment->order->id) }}" style="color: #2969FF;text-decoration: none;">
                                        #{{ $payment->order->increment_id ?? $payment->order_id }}
                                    </a>
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 15px;">
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td style="padding-bottom: 5px;">
                                <span style="font-size: 14px;color: #065F46;font-weight: 500;">
                                    @lang('banktransfer::app.emails.customer.payment-approved.order-total')
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span style="font-size: 18px;color: #121A26;font-weight: 700;">
                                    {{ core()->formatPrice($payment->order->grand_total, $payment->order->order_currency_code) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td style="padding-bottom: 5px;">
                                <span style="font-size: 14px;color: #065F46;font-weight: 500;">
                                    @lang('banktransfer::app.emails.customer.payment-approved.approved-date')
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span style="font-size: 16px;color: #121A26;">
                                    {{ core()->formatDate($payment->reviewed_at, 'Y-m-d H:i:s') }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div style="background: #F9FAFB;padding: 20px;border-radius: 8px;margin-bottom: 30px;">
        <p style="font-size: 16px;color: #384860;line-height: 24px;margin: 0;">
            @lang('banktransfer::app.emails.customer.payment-approved.next-steps')
        </p>
    </div>

    <div style="margin-bottom: 40px;">
        <a
            href="{{ route('shop.customers.account.orders.view', $payment->order->id) }}"
            style="padding: 16px 45px;justify-content: center;align-items: center;gap: 10px;border-radius: 2px;background: #060C3B;color: #FFFFFF;text-decoration: none;text-transform: uppercase;font-weight: 700;display: inline-block;"
        >
            @lang('banktransfer::app.emails.customer.payment-approved.track-order-button')
        </a>
    </div>

    <p style="font-size: 14px;color: #6B7280;line-height: 20px;">
        @lang('banktransfer::app.emails.customer.payment-approved.footer-note')
    </p>
@endcomponent
