<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Webkul\Payment\Enums\PaymentMethodCode;
use Webkul\Payment\Enums\PaymentPurpose;
use Webkul\Payment\Enums\PaymentStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_transfer_payments', function (Blueprint $table) {
            $table->integer('order_id')->unsigned()->nullable()->change();
            $table->integer('payment_id')->unsigned()->nullable()->after('id');
            $table->string('receipt_disk')->nullable()->after('slip_path');
            $table->string('receipt_name')->nullable()->after('receipt_disk');
            $table->string('receipt_mime')->nullable()->after('receipt_name');
            $table->unsignedBigInteger('receipt_size')->nullable()->after('receipt_mime');
            $table->string('bank_account_key')->nullable()->after('transaction_reference');

            $table->unique('payment_id');
            $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
        });

        DB::table('bank_transfer_payments')
            ->orderBy('id')
            ->chunkById(50, function ($rows) {
                foreach ($rows as $row) {
                    if ($row->payment_id) {
                        continue;
                    }

                    $order = DB::table('orders')->where('id', $row->order_id)->first();

                    if (! $order) {
                        continue;
                    }

                    $status = match ($row->status) {
                        'approved' => PaymentStatus::PAID->value,
                        'rejected' => PaymentStatus::REJECTED->value,
                        default => PaymentStatus::PENDING_REVIEW->value,
                    };

                    $paidAt = $row->status === 'approved' ? $row->reviewed_at : null;
                    $approvedAt = $row->status === 'approved' ? $row->reviewed_at : null;
                    $rejectedAt = $row->status === 'rejected' ? $row->reviewed_at : null;
                    $fulfilledAt = $row->status === 'approved' ? $row->reviewed_at : null;

                    $paymentId = DB::table('payments')->insertGetId([
                        'customer_id' => $row->customer_id,
                        'payable_type' => 'Webkul\\Sales\\Models\\Order',
                        'payable_id' => $row->order_id,
                        'payment_method' => PaymentMethodCode::BANK_TRANSFER->value,
                        'purpose' => PaymentPurpose::ORDER_PAYMENT->value,
                        'amount' => $order->base_grand_total,
                        'currency' => $order->base_currency_code,
                        'status' => $status,
                        'settlement_key' => 'legacy-banktransfer:'.$row->id.':'.Str::uuid(),
                        'external_reference' => $row->transaction_reference,
                        'notes' => null,
                        'admin_notes' => $row->admin_note,
                        'reviewed_by' => $row->reviewed_by,
                        'reviewed_at' => $row->reviewed_at,
                        'approved_at' => $approvedAt,
                        'rejected_at' => $rejectedAt,
                        'paid_at' => $paidAt,
                        'fulfilled_at' => $fulfilledAt,
                        'rejection_reason' => $row->status === 'rejected' ? $row->admin_note : null,
                        'meta' => json_encode(['legacy_bank_transfer_id' => $row->id]),
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);

                    DB::table('bank_transfer_payments')
                        ->where('id', $row->id)
                        ->update([
                            'payment_id' => $paymentId,
                            'receipt_disk' => 'private',
                            'receipt_name' => basename($row->slip_path),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('bank_transfer_payments', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropUnique(['payment_id']);
            $table->dropColumn([
                'payment_id',
                'receipt_disk',
                'receipt_name',
                'receipt_mime',
                'receipt_size',
                'bank_account_key',
            ]);
            $table->integer('order_id')->unsigned()->nullable(false)->change();
        });
    }
};
