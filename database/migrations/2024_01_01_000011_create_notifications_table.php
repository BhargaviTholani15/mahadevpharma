<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('channel', ['SMS', 'EMAIL', 'PUSH', 'IN_APP', 'WHATSAPP']);
            $table->string('event_type', 50)->index();
            $table->string('title', 255);
            $table->text('body');
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->enum('status', ['QUEUED', 'SENT', 'DELIVERED', 'FAILED', 'READ'])->default('QUEUED')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->string('failure_reason', 255)->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('action', 100)->index();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 255);
            $table->string('gst_number', 15);
            $table->string('drug_license_no', 50);
            $table->char('state_code', 2);
            $table->string('address_line1', 255);
            $table->string('city', 100);
            $table->string('state', 100);
            $table->char('pincode', 6);
            $table->string('phone', 15);
            $table->string('email', 255)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->string('invoice_prefix', 10)->default('INV');
            $table->unsignedBigInteger('current_invoice_seq')->default(0);
            $table->char('financial_year', 7);
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notifications');
    }
};
