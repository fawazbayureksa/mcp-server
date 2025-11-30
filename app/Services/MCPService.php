<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\Customer;
use App\Models\MCPLog;
use Illuminate\Http\Request;

class MCPService
{
    public function getTools()
    {
        return [
            [
                'name' => 'get_user',
                'description' => 'Retrieve user information by email',
                'args' => [
                    'type' => 'object',
                    'properties' => [
                        'email' => ['type' => 'string', 'description' => 'User email address']
                    ],
                    'required' => ['email']
                ]
            ],
            [
                'name' => 'update_task_status',
                'description' => 'Update the status of a task',
                'args' => [
                    'type' => 'object',
                    'properties' => [
                        'task_id' => ['type' => 'integer', 'description' => 'Task ID'],
                        'status' => ['type' => 'string', 'enum' => ['pending', 'in_progress', 'completed'], 'description' => 'New status']
                    ],
                    'required' => ['task_id', 'status']
                ]
            ],
            [
                'name' => 'search_customer',
                'description' => 'Search customers by keyword',
                'args' => [
                    'type' => 'object',
                    'properties' => [
                        'keyword' => ['type' => 'string', 'description' => 'Search keyword']
                    ],
                    'required' => ['keyword']
                ]
            ],
            [
                'name' => 'get_pending_tasks',
                'description' => 'Get pending tasks with optional limit',
                'args' => [
                    'type' => 'object',
                    'properties' => [
                        'limit' => ['type' => 'integer', 'description' => 'Maximum number of tasks to return', 'default' => 10]
                    ]
                ]
            ]
        ];
    }

    public function executeTool(string $toolName, array $args, Request $request)
    {
        $method = 'handle' . str_replace('_', '', ucwords($toolName, '_'));

        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException("Tool '{$toolName}' not found");
        }

        $result = $this->{$method}($args);

        // Log the request
        MCPLog::create([
            'tool_name' => $toolName,
            'args' => json_encode($args),
            'response' => json_encode($result),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $result;
    }

    private function handleGetUser(array $args)
    {
        $user = User::where('email', $args['email'])->first();

        if (!$user) {
            return ['error' => 'User not found'];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    private function handleUpdateTaskStatus(array $args)
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

    private function handleSearchCustomer(array $args)
    {
        $customers = Customer::where('name', 'like', '%' . $args['keyword'] . '%')
            ->orWhere('email', 'like', '%' . $args['keyword'] . '%')
            ->get();

        return $customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ];
        })->toArray();
    }

    private function handleGetPendingTasks(array $args)
    {
        $limit = $args['limit'] ?? 10;

        $tasks = Task::where('status', 'pending')->limit($limit)->get();

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

    public function getResources()
    {
        return [
            [
                'name' => 'users',
                'description' => 'User data schema',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => ['type' => 'string'],
                        'email' => ['type' => 'string'],
                    ]
                ]
            ],
            [
                'name' => 'tasks',
                'description' => 'Task data schema',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'title' => ['type' => 'string'],
                        'description' => ['type' => 'string'],
                        'status' => ['type' => 'string', 'enum' => ['pending', 'in_progress', 'completed']],
                        'user_id' => ['type' => 'integer'],
                    ]
                ]
            ],
            [
                'name' => 'customers',
                'description' => 'Customer data schema',
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => ['type' => 'string'],
                        'email' => ['type' => 'string'],
                        'phone' => ['type' => 'string'],
                    ]
                ]
            ]
        ];
    }
}