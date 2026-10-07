<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->unsignedBigInteger('additional_storage_bytes');
            $table->unsignedInteger('price_paise');
            $table->char('currency', 3)->default('INR');
            $table->string('billing_interval', 16)->default('month');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('storage_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('storage_plan_id')->constrained('storage_plans')->restrictOnDelete();
            $table->string('status', 32)->default('pending');
            $table->unsignedBigInteger('additional_storage_bytes');
            $table->unsignedInteger('price_paise');
            $table->char('currency', 3)->default('INR');
            $table->string('billing_interval', 16)->default('month');
            $table->string('payment_provider', 32)->nullable();
            $table->string('provider_subscription_id', 191)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('current_period_starts_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->unique(['payment_provider', 'provider_subscription_id'], 'subscriptions_provider_unique');
            $table->index(['user_id', 'status', 'current_period_ends_at'], 'subscriptions_entitlement_index');
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('storage_subscription_id')->constrained('storage_subscriptions')->restrictOnDelete();
            $table->string('payment_provider', 32);
            $table->string('provider_order_id', 191)->nullable();
            $table->string('provider_payment_id', 191)->nullable();
            $table->string('idempotency_key', 191)->unique();
            $table->unsignedInteger('amount_paise');
            $table->unsignedInteger('refunded_amount_paise')->default(0);
            $table->char('currency', 3)->default('INR');
            $table->string('status', 32)->default('pending');
            $table->timestamp('period_starts_at')->nullable();
            $table->timestamp('period_ends_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->unique(['payment_provider', 'provider_payment_id'], 'payments_provider_unique');
            $table->index(['storage_subscription_id', 'status'], 'payments_subscription_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('storage_subscriptions');
        Schema::dropIfExists('storage_plans');
    }
};
