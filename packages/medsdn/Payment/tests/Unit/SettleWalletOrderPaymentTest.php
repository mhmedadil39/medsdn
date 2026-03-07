<?php

namespace Webkul\Payment\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Payment\Actions\PayOrderWithWalletAction;
use Webkul\Payment\Enums\PaymentMethodCode;
use Webkul\Payment\Listeners\SettleWalletOrderPayment;
use Webkul\Sales\Models\Order;

class SettleWalletOrderPaymentTest extends TestCase
{
    public function test_it_ignores_non_wallet_orders(): void
    {
        $action = $this->createMock(PayOrderWithWalletAction::class);
        $customers = $this->createMock(CustomerRepository::class);

        $action->expects($this->never())->method('handle');
        $customers->expects($this->never())->method('find');

        $listener = new SettleWalletOrderPayment($action, $customers);
        $order = $this->makeOrder('cashondelivery', 12, 45);

        $listener->handle($order);
    }

    public function test_it_requires_a_customer_id_for_wallet_orders(): void
    {
        $listener = new SettleWalletOrderPayment(
            $this->createMock(PayOrderWithWalletAction::class),
            $this->createMock(CustomerRepository::class)
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Wallet payment requires an authenticated customer order.');

        $listener->handle($this->makeOrder(PaymentMethodCode::WALLET->value, null, 91));
    }

    public function test_it_throws_when_customer_cannot_be_resolved(): void
    {
        $action = $this->createMock(PayOrderWithWalletAction::class);
        $customers = $this->createMock(CustomerRepository::class);

        $action->expects($this->never())->method('handle');
        $customers->expects($this->once())
            ->method('find')
            ->with(33)
            ->willReturn(null);

        $listener = new SettleWalletOrderPayment($action, $customers);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Customer not found for wallet payment.');

        $listener->handle($this->makeOrder(PaymentMethodCode::WALLET->value, 33, 91));
    }

    public function test_it_settles_wallet_orders_once_customer_is_resolved(): void
    {
        $action = $this->createMock(PayOrderWithWalletAction::class);
        $customers = $this->createMock(CustomerRepository::class);

        $order = $this->makeOrder(PaymentMethodCode::WALLET->value, 77, 88);
        $customer = new Customer();
        $customer->id = 77;

        $customers->expects($this->once())
            ->method('find')
            ->with(77)
            ->willReturn($customer);

        $action->expects($this->once())
            ->method('handle')
            ->with(
                $order,
                $customer,
                $this->callback(function (array $attributes) {
                    return $attributes['settlement_key'] === 'order:88:wallet'
                        && $attributes['notes'] === 'Order paid with wallet at checkout'
                        && $attributes['meta']['source'] === 'checkout.order.save.after';
                })
            );

        $listener = new SettleWalletOrderPayment($action, $customers);

        $listener->handle($order);
    }

    protected function makeOrder(string $paymentMethod, ?int $customerId, int $orderId): Order
    {
        $order = new Order();
        $order->id = $orderId;
        $order->customer_id = $customerId;
        $order->setRelation('payment', (object) ['method' => $paymentMethod]);

        return $order;
    }
}
