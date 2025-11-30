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
        Schema::create('mcp_command_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index(); // For conversation context
            $table->text('user_message'); // Original user input
            $table->json('ai_analysis')->nullable(); // AI's intent analysis
            $table->string('detected_tool')->nullable(); // Tool that was called
            $table->json('extracted_args')->nullable(); // Arguments extracted
            $table->json('tool_result')->nullable(); // Result from tool execution
            $table->text('ai_response')->nullable(); // Human-readable response
            $table->string('status')->default('success'); // success, error, permission_denied
            $table->text('error_message')->nullable();
            $table->string('ip_address');
            $table->json('metadata')->nullable(); // Additional context
            $table->timestamps();

            $table->index(['session_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcp_command_logs');
    }
};
