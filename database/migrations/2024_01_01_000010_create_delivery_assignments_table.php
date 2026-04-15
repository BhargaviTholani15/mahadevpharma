<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('assigned_to')->constrained('users');
            $table->foreignId('assigned_by')->constrained('users');
            $table->enum('status', [
                'ASSIGNED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED',
                'FAILED', 'RETURNED', 'REASSIGNED'
            ])->default('ASSIGNED')->index();
            $table->string('delivery_otp', 6)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->date('scheduled_date')->nullable()->index();
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('delivery_lat', 10, 7)->nullable();
            $table->decimal('delivery_lng', 10, 7)->nullable();
            $table->string('failure_reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_assignments');
    }
};
