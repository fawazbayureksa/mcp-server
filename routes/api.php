<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('mcp.auth')->group(function () {
    Route::get('/mcp/tools', [App\Http\Controllers\MCPController::class, 'getTools']);
    Route::post('/mcp/tool', [App\Http\Controllers\MCPController::class, 'executeTool']);
    Route::post('/mcp/agent-call', [App\Http\Controllers\MCPController::class, 'agentCall']);
    Route::get('/mcp/tool-schemas', [App\Http\Controllers\MCPController::class, 'getToolSchemas']);
    Route::get('/mcp/resources', [App\Http\Controllers\MCPController::class, 'getResources']);

    // AI Chat Interface
    Route::post('/mcp/chat', [App\Http\Controllers\ChatController::class, 'chat']);
    Route::get('/mcp/chat/history', [App\Http\Controllers\ChatController::class, 'getHistory']);
    Route::delete('/mcp/chat/history', [App\Http\Controllers\ChatController::class, 'clearHistory']);
    Route::get('/mcp/sessions', [App\Http\Controllers\MCPController::class, 'getSessions']);
});
