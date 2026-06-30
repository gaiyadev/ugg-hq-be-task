<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot: users ↔ roles (many-to-many).
     * Unique composite key prevents duplicate role assignments.
     */
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {

            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUuid('role_id')
                ->constrained('roles')
                ->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            // Prevent duplicate role assignments
            $table->unique(['user_id', 'role_id']);
            $table->index(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
