<?php

namespace App\Http\Controllers;

use App\Services\MCPService;
use App\MCP\Tools\ToolRegistry;
use App\Models\MCPCommandLog;
use App\Models\MCPLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MCPController extends Controller
{
    protected MCPService $mcpService;

    public function __construct(MCPService $mcpService)
    {
        $this->mcpService = $mcpService;
        ToolRegistry::autoDiscover();
    }

    public function getTools(): JsonResponse
    {
        $tools = array_map(function ($tool) {
            return $tool->toArray();
        }, ToolRegistry::getAllTools());

        return response()->json([
            'success' => true,
            'data' => $tools,
            'message' => 'Tools retrieved successfully'
        ]);
    }

    public function executeTool(Request $request): JsonResponse
    {
        $request->validate([
            'tool_name' => 'required|string',
            'args' => 'required|array'
        ]);

        try {
            $result = $this->mcpService->executeTool($request->tool_name, $request->args, $request);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Tool executed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function agentCall(Request $request): JsonResponse
    {
        $request->validate([
            'tool' => 'required|string',
            'args' => 'sometimes|array'
        ]);

        $tool = ToolRegistry::getTool($request->tool);

        if (!$tool) {
            return response()->json([
                'success' => false,
                'error' => 'Tool not found'
            ], 404);
        }

        try {
            $result = $tool->execute($request->args ?? []);

            // Log the request
            MCPLog::create([
                'tool_name' => $request->tool,
                'args' => json_encode($request->args ?? []),
                'response' => json_encode($result),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getToolSchemas(): JsonResponse
    {
        $schemas = ToolRegistry::getToolSchemas();

        return response()->json([
            'success' => true,
            'data' => $schemas,
            'message' => 'Tool schemas retrieved successfully'
        ]);
    }

    public function getResources(): JsonResponse
    {
        $resources = $this->mcpService->getResources();

        return response()->json([
            'success' => true,
            'data' => $resources,
            'message' => 'Resources retrieved successfully'
        ]);
    }

    public function getSessions(): JsonResponse
    {
        $sessions = MCPCommandLog::select('session_id', DB::raw('MAX(created_at) as created_at'))
            ->groupBy('session_id')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->session_id,
                    'name' => 'Session ' . substr($session->session_id, -8), // Last 8 chars for name
                    'created_at' => $session->created_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sessions,
            'message' => 'Sessions retrieved successfully'
        ]);
    }
}
