<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->foreignId('parent_id')->nullable()->constrained('categories');
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 150)->unique();
            $table->string('manufacturer', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hsn_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);
            $table->string('description', 255);
            $table->decimal('cgst_rate', 5, 2);
            $table->decimal('sgst_rate', 5, 2);
            $table->decimal('igst_rate', 5, 2);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->unique(['code', 'effective_from']);
            $table->index('effective_to');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->index();
            $table->string('generic_name', 255)->nullable()->index();
            $table->foreignId('brand_id')->nullable()->constrained('brands');
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->foreignId('hsn_code_id')->constrained('hsn_codes');
            $table->string('dosage_form', 50)->nullable();
            $table->string('strength', 50)->nullable();
            $table->string('pack_size', 50)->nullable();
            $table->string('sku', 50)->unique();
            $table->boolean('is_prescription_only')->default(false);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_active', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('hsn_codes');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
