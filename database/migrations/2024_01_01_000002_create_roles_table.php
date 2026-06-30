<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Roles table.
     * is_system flag prevents system roles (super-admin, admin) from being deleted.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();        // e.g. "Super Admin"
            $table->string('slug')->unique();        // e.g. "super-admin"
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false); // protects from deletion
            $table->timestamps();

            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
