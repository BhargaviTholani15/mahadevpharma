<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('contact_person', 150)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 15);
            $table->string('gst_number', 15)->nullable();
            $table->string('drug_license_no', 50)->nullable();
            $table->string('address_line1', 255);
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('state_code', 2);
            $table->string('pincode', 6);
            $table->integer('payment_terms_days')->default(30);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index('is_active');
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->enum('status', [
                'DRAFT', 'SENT', 'PARTIALLY_RECEIVED', 'RECEIVED', 'CANCELLED'
            ])->default('DRAFT')->index();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->date('expected_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['supplier_id', 'status']);
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->string('batch_number', 50)->nullable();
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->date('mfg_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('mrp', 10, 2)->nullable();
            $table->timestamps();

            $table->index('purchase_order_id');
            $table->index('product_id');
        });

        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number', 50)->unique();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices');
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->enum('status', [
                'REQUESTED', 'APPROVED', 'RECEIVED', 'REJECTED', 'CREDIT_ISSUED'
            ])->default('REQUESTED')->index();
            $table->enum('reason', [
                'DAMAGED', 'EXPIRED', 'WRONG_PRODUCT', 'QUALITY_ISSUE', 'EXCESS_QUANTITY', 'OTHER'
            ]);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['client_id', 'status']);
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained('sales_returns')->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('batch_id')->constrained('batches');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->enum('condition', ['GOOD', 'DAMAGED', 'EXPIRED']);
            $table->timestamps();

            $table->index('sales_return_id');
            $table->index('product_id');
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 50)->unique();
            $table->foreignId('from_warehouse_id')->constrained('warehouses');
            $table->foreignId('to_warehouse_id')->constrained('warehouses');
            $table->enum('status', [
                'DRAFT', 'APPROVED', 'IN_TRANSIT', 'RECEIVED', 'CANCELLED'
            ])->default('DRAFT')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('batch_id')->constrained('batches');
            $table->integer('quantity');
            $table->timestamps();

            $table->index('stock_transfer_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
    }
};
