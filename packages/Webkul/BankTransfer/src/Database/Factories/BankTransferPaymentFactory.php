<?php

namespace Webkul\BankTransfer\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\BankTransfer\Models\BankTransferPayment;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;
use Webkul\User\Models\Admin;

class BankTransferPaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BankTransferPayment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'customer_id' => Customer::factory(),
            'method_code' => 'banktransfer',
            'transaction_reference' => $this->faker->optional()->numerify('TXN-########'),
            'slip_path' => 'bank-transfers/' . $this->faker->numberBetween(1, 1000) . '/' . $this->faker->uuid() . '.jpg',
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'admin_note' => null,
        ];
    }

    /**
     * Indicate that the payment is approved.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function approved(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
                'reviewed_by' => Admin::factory(),
                'reviewed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
                'admin_note' => $this->faker->optional()->sentence(),
            ];
        });
    }

    /**
     * Indicate that the payment is rejected.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function rejected(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'reviewed_by' => Admin::factory(),
                'reviewed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
                'admin_note' => $this->faker->sentence(),
            ];
        });
    }

    /**
     * Indicate that the payment is pending.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pending(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'admin_note' => null,
            ];
        });
    }

    /**
     * Indicate that the payment has a transaction reference.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withTransactionReference(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'transaction_reference' => $this->faker->numerify('TXN-########'),
            ];
        });
    }

    /**
     * Indicate that the payment has no transaction reference.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withoutTransactionReference(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'transaction_reference' => null,
            ];
        });
    }
}
