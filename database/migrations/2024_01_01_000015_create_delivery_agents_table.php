<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_agents', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('phone', 15)->unique();
            $table->string('alt_phone', 15)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('vehicle_type', 50)->nullable();
            $table->string('vehicle_number', 20)->nullable();
            $table->string('license_number', 30)->nullable();
            $table->string('license_expiry', 10)->nullable();
            $table->string('zone', 100)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('emergency_contact', 15)->nullable();
            $table->string('id_proof_type', 50)->nullable();
            $table->string('id_proof_number', 50)->nullable();
            $table->date('joining_date')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'is_available']);
        });

        // Add delivery_agent_id to delivery_assignments
        Schema::table('delivery_assignments', function (Blueprint $table) {
            $table->foreignId('delivery_agent_id')->nullable()->after('assigned_to')->constrained('delivery_agents');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_assignments', function (Blueprint $table) {
            $table->dropForeign(['delivery_agent_id']);
            $table->dropColumn('delivery_agent_id');
        });
        Schema::dropIfExists('delivery_agents');
    }
};
