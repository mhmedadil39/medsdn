<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Webkul\Wallet\Enums\WalletTransactionStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('wallet_id')->unsigned();
            $table->integer('customer_id')->unsigned();
            $table->string('type');
            $table->string('direction', 10);
            $table->decimal('amount', 12, 4);
            $table->decimal('balance_before', 12, 4);
            $table->decimal('balance_after', 12, 4);
            $table->string('status')->default(WalletTransactionStatus::COMPLETED->value);
            $table->string('reference_type')->nullable();
            $table->integer('reference_id')->nullable();
            $table->string('source')->nullable();
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->string('created_by_type')->nullable();
            $table->integer('created_by_id')->nullable();
            $table->string('entry_key')->unique();
            $table->timestamps();

            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index(['wallet_id', 'created_at']);
            $table->index(['customer_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
