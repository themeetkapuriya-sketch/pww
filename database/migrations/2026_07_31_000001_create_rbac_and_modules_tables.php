<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Roles Table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Role Permissions Mapping Table
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role_slug');
            $table->string('permission_key');
            $table->timestamps();

            $table->unique(['role_slug', 'permission_key']);
        });

        // 3. Hierarchical Navigation Modules Table
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('route_name')->nullable();
            $table->string('icon_class')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('modules')->nullOnDelete();
            $table->string('permission_key')->nullable();
            $table->integer('order_weight')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
    }
};
