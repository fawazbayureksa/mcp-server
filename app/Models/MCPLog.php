<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MCPLog extends Model
{
    use HasFactory;

    protected $table = 'mcp_logs';

    protected $fillable = ['tool_name', 'args', 'response', 'ip_address', 'user_agent'];
}
