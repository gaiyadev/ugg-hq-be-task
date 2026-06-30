<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit logs table.
     * Event-sourcing style immutable audit trail.
     * 
     * Immutability: No updated_at, no soft delete → logs are never modified.
     * entity_type is a polymorphic-style string (e.g. "User", "Resource").
     * metadata stores JSON for flexible per-event data (e.g. old/new values on update).
     * ip_address: 45 chars supports IPv6.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Actor (nullable: system events have no actor)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // What happened
            $table->string('action');         // e.g. "login", "resource.created"
            $table->string('entity_type');    // e.g. "Resource", "User"
            $table->unsignedBigInteger('entity_id')->nullable(); // ID of affected entity
            $table->text('description');      // Human-readable description

            // Flexible payload
            $table->json('metadata')->nullable(); // old/new values, additional context

            // Request context
            $table->string('ip_address', 45)->nullable(); // IPv4 or IPv6
            $table->text('user_agent')->nullable();

            // Immutable: only created_at, never updated
            $table->timestamp('created_at')->useCurrent();

            // Indexes for dashboard queries and filtering
            $table->index('action');
            $table->index('entity_type');
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
