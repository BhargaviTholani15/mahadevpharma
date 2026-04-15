<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->date('entry_date');
            $table->enum('entry_type', [
                'INVOICE', 'PAYMENT', 'CREDIT_NOTE', 'DEBIT_NOTE', 'OPENING_BALANCE', 'ADJUSTMENT'
            ])->index();
            $table->decimal('debit_amount', 12, 2)->default(0);
            $table->decimal('credit_amount', 12, 2)->default(0);
            $table->decimal('running_balance', 12, 2);
            $table->string('reference_type', 50);
            $table->unsignedBigInteger('reference_id');
            $table->string('narration', 500)->nullable();
            $table->char('financial_year', 7);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['client_id', 'entry_date']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['client_id', 'financial_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
