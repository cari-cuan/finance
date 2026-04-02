<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $header) {
            $header->id();
            $header->string('name');
            $header->integer('duration_days');
            $header->decimal('price', 15, 2);
            $header->boolean('is_active')->default(true);
            $header->timestamps();
        });

        Schema::create('vouchers', function (Blueprint $header) {
            $header->id();
            $header->string('code')->unique();
            $header->enum('discount_type', ['percent', 'nominal']);
            $header->decimal('discount_value', 15, 2);
            $header->dateTime('expires_at')->nullable();
            $header->integer('quota')->default(100);
            $header->integer('used_count')->default(0);
            $header->boolean('is_active')->default(true);
            $header->timestamps();
        });

        Schema::create('orders', function (Blueprint $header) {
            $header->id();
            $header->string('order_number')->unique();
            $header->foreignId('user_id')->constrained();
            $header->foreignId('package_id')->constrained();
            $header->foreignId('voucher_id')->nullable()->constrained();
            $header->decimal('base_price', 15, 2);
            $header->decimal('discount_amount', 15, 2)->default(0);
            $header->decimal('total_amount', 15, 2);
            $header->string('status')->default('pending'); // pending, success, failed, expired
            $header->string('payment_token')->nullable();
            $header->string('payment_url')->nullable();
            $header->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('packages');
    }
};
