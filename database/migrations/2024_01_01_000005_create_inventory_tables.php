<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 20)->unique();
            $table->char('state_code', 2);
            $table->string('address_line1', 255);
            $table->string('city', 100);
            $table->string('state', 100);
            $table->char('pincode', 6);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->string('batch_number', 50);
            $table->date('mfg_date')->nullable();
            $table->date('expiry_date')->index();
            $table->decimal('mrp', 10, 2);
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['product_id', 'batch_number']);
        });

        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('batch_id')->constrained('batches');
            $table->integer('quantity')->default(0);
            $table->integer('reserved_qty')->default(0);
            $table->integer('reorder_level')->nullable();
            $table->timestamps();

            $table->unique(['warehouse_id', 'batch_id']);
            $table->index('quantity');
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->foreignId('batch_id')->constrained('batches');
            $table->enum('movement_type', [
                'PURCHASE_IN', 'SALE_OUT', 'RETURN_IN', 'RETURN_OUT',
                'TRANSFER_IN', 'TRANSFER_OUT', 'ADJUSTMENT', 'DAMAGED', 'EXPIRED'
            ])->index();
            $table->integer('quantity_change');
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reason', 255)->nullable();
            $table->foreignId('performed_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['warehouse_id', 'batch_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('warehouse_stocks');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('warehouses');
    }
};
