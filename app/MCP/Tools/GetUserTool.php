<?php

namespace App\MCP\Tools;

use App\Models\User;

class GetUserTool extends Tool
{
    public function getName(): string
    {
        return 'get_user';
    }

    public function getDescription(): string
    {
        return 'Retrieve user information by email address';
    }

    public function getParameters(): array
    {
        return [
            'email' => [
                'type' => 'string',
                'description' => 'User email address',
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
                'name' => ['type' => 'string'],
                'email' => ['type' => 'string'],
            ],
        ];
    }

    public function execute(array $args): array
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
}