<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('git_contexts', function (Blueprint $table) {
            $table->id();
            $table->string('repository_url');
            $table->string('repository_name');
            $table->string('branch_name');
            $table->string('commit_hash')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['repository_name', 'branch_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('git_contexts');
    }
};
