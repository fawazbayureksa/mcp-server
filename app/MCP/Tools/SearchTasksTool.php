<?php

namespace App\MCP\Tools;

use App\Models\Task;

class SearchTasksTool extends Tool
{
    public function getName(): string
    {
        return 'search_tasks';
    }

    public function getDescription(): string
    {
        return 'Search tasks by status and/or assigned user';
    }

    public function getParameters(): array
    {
        return [
            'status' => [
                'type' => 'string',
                'enum' => ['pending', 'in_progress', 'completed'],
                'description' => 'Task status filter',
            ],
            'assigned_to' => [
                'type' => 'integer',
                'description' => 'User ID the task is assigned to',
            ],
        ];
    }

    public function getReturns(): array
    {
        return [
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'title' => ['type' => 'string'],
                    'description' => ['type' => 'string'],
                    'status' => ['type' => 'string'],
                    'user_id' => ['type' => 'integer'],
                ],
            ],
        ];
    }

    public function execute(array $args): array
    {
        $query = Task::query();

        if (isset($args['status'])) {
            $query->where('status', $args['status']);
        }

        if (isset($args['assigned_to'])) {
            $query->where('user_id', $args['assigned_to']);
        }

        $tasks = $query->get();

        return $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'user_id' => $task->user_id,
            ];
        })->toArray();
    }
}