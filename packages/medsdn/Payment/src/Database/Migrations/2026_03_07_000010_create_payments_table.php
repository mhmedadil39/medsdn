<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Webkul\Payment\Enums\PaymentStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id')->unsigned()->nullable();
            $table->string('payable_type')->nullable();
            $table->integer('payable_id')->nullable();
            $table->string('payment_method');
            $table->string('purpose');
            $table->decimal('amount', 12, 4);
            $table->string('currency', 10);
            $table->string('status')->default(PaymentStatus::PENDING->value);
            $table->string('settlement_key')->unique();
            $table->string('external_reference')->nullable();
            $table->string('bank_name')->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->integer('reviewed_by')->unsigned()->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('admins')->nullOnDelete();
            $table->index(['payment_method', 'status']);
            $table->index(['purpose', 'status']);
            $table->index(['payable_type', 'payable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
