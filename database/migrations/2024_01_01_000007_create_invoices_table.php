<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->date('invoice_date');
            $table->date('due_date')->index();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('taxable_total', 12, 2);
            $table->decimal('cgst_total', 12, 2)->default(0);
            $table->decimal('sgst_total', 12, 2)->default(0);
            $table->decimal('igst_total', 12, 2)->default(0);
            $table->decimal('round_off', 5, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2);
            $table->enum('status', [
                'DRAFT', 'ISSUED', 'PARTIALLY_PAID', 'PAID', 'CANCELLED', 'CREDIT_NOTE'
            ])->default('ISSUED')->index();
            $table->boolean('is_inter_state');
            $table->char('supply_state_code', 2);
            $table->char('billing_state_code', 2);
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 255)->nullable();
            $table->timestamps();

            $table->index(['client_id', 'due_date']);
            $table->index('invoice_date');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('batch_id')->constrained('batches');
            $table->string('hsn_code', 10)->index();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('mrp', 10, 2);
            $table->decimal('discount_pct', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('taxable_amount', 12, 2);
            $table->decimal('cgst_rate', 5, 2)->default(0);
            $table->decimal('cgst_amount', 10, 2)->default(0);
            $table->decimal('sgst_rate', 5, 2)->default(0);
            $table->decimal('sgst_amount', 10, 2)->default(0);
            $table->decimal('igst_rate', 5, 2)->default(0);
            $table->decimal('igst_amount', 10, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->index('invoice_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
