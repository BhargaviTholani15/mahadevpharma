<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosage_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('strengths', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('pack_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('drug_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('description', 255)->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('storage_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_conditions');
        Schema::dropIfExists('drug_schedules');
        Schema::dropIfExists('pack_sizes');
        Schema::dropIfExists('strengths');
        Schema::dropIfExists('dosage_forms');
    }
};
