<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MCPCommandLog extends Model
{
    use HasFactory;
    protected $table = 'mcp_command_logs';
    protected $fillable = [
        'session_id',
        'user_message',
        'ai_analysis',
        'detected_tool',
        'extracted_args',
        'tool_result',
        'ai_response',
        'status',
        'error_message',
        'ip_address',
        'metadata',
    ];

    protected $casts = [
        'ai_analysis' => 'array',
        'extracted_args' => 'array',
        'tool_result' => 'array',
        'metadata' => 'array',
    ];
}
