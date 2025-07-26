<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('knowledge', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('git_context_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('content');
            $table->text('summary')->nullable();
            $table->string('type')->default('note'); // note, solution, command, snippet
            $table->json('metadata')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamp('captured_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['is_public', 'created_at']);

            // Only add fulltext for MySQL/PostgreSQL, not SQLite
            if (DB::getDriverName() !== 'sqlite') {
                $table->fullText(['title', 'content', 'summary']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge');
    }
};
