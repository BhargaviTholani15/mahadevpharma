<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Business Details
            $table->string('business_type', 50)->nullable()->after('proprietor_name');
            $table->string('fssai_number', 20)->nullable()->after('pan_number');
            $table->string('dl_expiry_date', 10)->nullable()->after('drug_license_no');

            // Contact
            $table->string('alt_phone', 15)->nullable()->after('pincode');
            $table->string('contact_person', 150)->nullable()->after('alt_phone');
            $table->string('contact_designation', 100)->nullable()->after('contact_person');

            // Delivery
            $table->string('delivery_address', 500)->nullable()->after('contact_designation');
            $table->string('delivery_city', 100)->nullable()->after('delivery_address');
            $table->string('delivery_pincode', 6)->nullable()->after('delivery_city');
            $table->string('delivery_instructions', 500)->nullable()->after('delivery_pincode');
            $table->string('preferred_delivery_time', 50)->nullable()->after('delivery_instructions');

            // Banking
            $table->string('bank_name', 100)->nullable()->after('preferred_delivery_time');
            $table->string('bank_account_no', 30)->nullable()->after('bank_name');
            $table->string('bank_ifsc', 11)->nullable()->after('bank_account_no');
            $table->string('bank_branch', 100)->nullable()->after('bank_ifsc');

            // Notes
            $table->text('notes')->nullable()->after('bank_branch');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'business_type', 'fssai_number', 'dl_expiry_date',
                'alt_phone', 'contact_person', 'contact_designation',
                'delivery_address', 'delivery_city', 'delivery_pincode',
                'delivery_instructions', 'preferred_delivery_time',
                'bank_name', 'bank_account_no', 'bank_ifsc', 'bank_branch',
                'notes',
            ]);
        });
    }
};
