<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 30)->unique();
            $table->foreignId('client_id')->constrained('clients');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', [
                'CASH', 'UPI', 'BANK_TRANSFER', 'CHEQUE', 'PAYMENT_GATEWAY', 'CREDIT_NOTE'
            ]);
            $table->date('payment_date');
            $table->string('reference_number', 100)->nullable();
            $table->string('gateway_txn_id', 255)->nullable();
            $table->enum('status', [
                'PENDING', 'CONFIRMED', 'BOUNCED', 'REVERSED', 'CANCELLED'
            ])->default('CONFIRMED')->index();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'payment_date']);
            $table->index('payment_method');
        });

        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->decimal('amount', 12, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->index('payment_id');
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
    }
};
