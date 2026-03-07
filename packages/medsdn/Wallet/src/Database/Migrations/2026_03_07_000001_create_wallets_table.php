<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Webkul\Wallet\Enums\WalletStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id')->unsigned();
            $table->string('currency', 10);
            $table->decimal('balance', 12, 4)->default(0);
            $table->decimal('available_balance', 12, 4)->default(0);
            $table->decimal('held_balance', 12, 4)->default(0);
            $table->string('status')->default(WalletStatus::ACTIVE->value);
            $table->timestamps();

            $table->unique('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
