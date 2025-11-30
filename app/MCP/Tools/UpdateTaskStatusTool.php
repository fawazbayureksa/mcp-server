<?php

namespace App\MCP\Tools;

use App\Models\Task;

class UpdateTaskStatusTool extends Tool
{
    public function getName(): string
    {
        return 'update_task_status';
    }

    public function getDescription(): string
    {
        return 'Update the status of a task';
    }

    public function getParameters(): array
    {
        return [
            'task_id' => [
                'type' => 'integer',
                'description' => 'Task ID',
                'required' => true,
            ],
            'status' => [
                'type' => 'string',
                'enum' => ['pending', 'in_progress', 'completed'],
                'description' => 'New status',
                'required' => true,
            ],
        ];
    }

    public function getReturns(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'title' => ['type' => 'string'],
                'status' => ['type' => 'string'],
            ],
        ];
    }

    public function execute(array $args): array
    {
        $task = Task::find($args['task_id']);

        if (!$task) {
            return ['error' => 'Task not found'];
        }

        $task->update(['status' => $args['status']]);

        return [
            'id' => $task->id,
            'title' => $task->title,
            'status' => $task->status,
        ];
    }
}