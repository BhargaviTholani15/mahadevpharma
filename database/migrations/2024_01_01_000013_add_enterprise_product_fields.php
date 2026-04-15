<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Pharmaceutical Details
            $table->text('composition')->nullable()->after('generic_name');
            $table->string('route_of_administration', 50)->nullable()->after('dosage_form');
            $table->string('schedule_type', 20)->nullable()->after('is_prescription_only');
            $table->string('storage_conditions', 100)->nullable()->after('schedule_type');
            $table->integer('shelf_life_months')->nullable()->after('storage_conditions');
            $table->boolean('is_controlled')->default(false)->after('shelf_life_months');
            $table->boolean('is_returnable')->default(true)->after('is_controlled');

            // Manufacturer & Regulatory
            $table->string('manufacturer_name', 255)->nullable()->after('is_returnable');
            $table->text('manufacturer_address')->nullable()->after('manufacturer_name');
            $table->string('country_of_origin', 100)->default('India')->after('manufacturer_address');
            $table->string('marketing_authorization', 100)->nullable()->after('country_of_origin');

            // Default Pricing
            $table->decimal('mrp', 12, 2)->nullable()->after('marketing_authorization');
            $table->decimal('purchase_price', 12, 2)->nullable()->after('mrp');
            $table->decimal('selling_price', 12, 2)->nullable()->after('purchase_price');
            $table->decimal('ptr', 12, 2)->nullable()->after('selling_price');
            $table->decimal('pts', 12, 2)->nullable()->after('ptr');
            $table->decimal('margin_pct', 5, 2)->nullable()->after('pts');

            // Inventory Defaults
            $table->integer('min_stock_level')->default(0)->after('margin_pct');
            $table->integer('reorder_level')->default(0)->after('min_stock_level');
            $table->integer('reorder_quantity')->default(0)->after('reorder_level');
            $table->integer('lead_time_days')->nullable()->after('reorder_quantity');
            $table->string('rack_location', 50)->nullable()->after('lead_time_days');

            // Description & Media
            $table->text('description')->nullable()->after('rack_location');
            $table->text('usage_instructions')->nullable()->after('description');
            $table->text('side_effects')->nullable()->after('usage_instructions');
            $table->string('image_url', 500)->nullable()->after('side_effects');
            $table->string('barcode', 50)->nullable()->unique()->after('image_url');
            $table->json('tags')->nullable()->after('barcode');

            // Batch Configuration
            $table->string('batch_prefix', 20)->nullable()->after('tags');
            $table->integer('near_expiry_alert_days')->default(90)->after('batch_prefix');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'composition', 'route_of_administration', 'schedule_type',
                'storage_conditions', 'shelf_life_months', 'is_controlled', 'is_returnable',
                'manufacturer_name', 'manufacturer_address', 'country_of_origin', 'marketing_authorization',
                'mrp', 'purchase_price', 'selling_price', 'ptr', 'pts', 'margin_pct',
                'min_stock_level', 'reorder_level', 'reorder_quantity', 'lead_time_days', 'rack_location',
                'description', 'usage_instructions', 'side_effects', 'image_url', 'barcode', 'tags',
                'batch_prefix', 'near_expiry_alert_days',
            ]);
        });
    }
};
