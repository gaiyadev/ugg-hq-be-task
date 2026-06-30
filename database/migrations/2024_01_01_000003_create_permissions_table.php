<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permissions table.
     * 'group' column groups permissions for display (e.g. "Users", "Resources").
     * 'slug' follows dot-notation convention: resource.action (e.g. users.create).
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();    // e.g. "Create Users"
            $table->string('slug')->unique();    // e.g. "users.create"
            $table->string('group');             // e.g. "Users"
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
