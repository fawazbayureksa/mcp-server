<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MCPAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $mcpKey = $request->header('X-MCP-Key');

        if (!$mcpKey) {
            return response()->json(['error' => 'X-MCP-Key header is required'], 401);
        }

        $validKeys = config('mcp.valid_keys', []);

        if (!in_array($mcpKey, $validKeys)) {
            return response()->json(['error' => 'Invalid MCP key'], 403);
        }

        return $next($request);
    }
}
