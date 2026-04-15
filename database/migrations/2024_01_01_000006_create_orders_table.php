<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->enum('status', [
                'DRAFT', 'PENDING', 'APPROVED', 'PACKED',
                'OUT_FOR_DELIVERY', 'DELIVERED', 'CANCELLED', 'RETURNED'
            ])->default('PENDING')->index();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->decimal('cgst_total', 12, 2)->default(0);
            $table->decimal('sgst_total', 12, 2)->default(0);
            $table->decimal('igst_total', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->boolean('is_credit_order')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('packed_by')->nullable()->constrained('users');
            $table->timestamp('packed_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 255)->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['client_id', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('batch_id')->constrained('batches');
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
            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
