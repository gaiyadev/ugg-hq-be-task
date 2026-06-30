<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Resources table.
     * Core entity of the system: title, description, lifecycle status.
     * 
     * Status lifecycle:
     *   draft → pending → approved → rejected
     *               ↑
     *            (can revert to draft)
     * 
     * Audit trail:
     *   - created_by / updated_by tracked with nullable (for system-created)
     *   - approved_by / approved_at tracked separately for approval audit
     *   - signature: SHA256 from SignatureService for tamper detection
     *   - Soft deletes: never hard-delete resources, supports recovery
     */
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('draft'); // draft|pending|approved|rejected

            // Signature for mock off-chain verification
            $table->string('signature', 64)->nullable(); // SHA256 = 64 hex chars

            // Audit trail columns
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for filtering/searching
            $table->index('status');
            $table->index('created_by');
            $table->index('created_at');
            $table->index('title'); // For LIKE searches (consider pg_trgm in production)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
