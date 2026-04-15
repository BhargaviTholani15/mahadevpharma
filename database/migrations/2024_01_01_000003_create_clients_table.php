<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users');
            $table->string('business_name', 255);
            $table->string('proprietor_name', 150)->nullable();
            $table->string('drug_license_no', 50)->unique();
            $table->string('gst_number', 15)->nullable()->index();
            $table->string('pan_number', 10)->nullable();
            $table->char('state_code', 2)->index();
            $table->string('address_line1', 255);
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100);
            $table->string('district', 100)->nullable();
            $table->string('state', 100);
            $table->char('pincode', 6);
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('current_outstanding', 12, 2)->default(0);
            $table->unsignedSmallInteger('credit_period_days')->default(30);
            $table->boolean('kyc_verified')->default(false);
            $table->timestamp('kyc_verified_at')->nullable();
            $table->foreignId('kyc_verified_by')->nullable()->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_active', 'deleted_at']);
            $table->index('current_outstanding');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
