<?php

namespace App\Http\Controllers;

use App\Services\MCPService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MCPController extends Controller
{
    protected MCPService $mcpService;

    public function __construct(MCPService $mcpService)
    {
        $this->mcpService = $mcpService;
    }

    public function getTools(): JsonResponse
    {
        $tools = $this->mcpService->getTools();

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

    public function getResources(): JsonResponse
    {
        $resources = $this->mcpService->getResources();

        return response()->json([
            'success' => true,
            'data' => $resources,
            'message' => 'Resources retrieved successfully'
        ]);
    }
}
