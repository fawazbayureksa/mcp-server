<?php

namespace App\MCP\Tools;

use App\Models\Customer;

class CreateCustomerTool extends Tool
{
    public function getName(): string
    {
        return 'create_customer';
    }

    public function getDescription(): string
    {
        return 'Create a new customer';
    }

    public function getParameters(): array
    {
        return [
            'name' => [
                'type' => 'string',
                'description' => 'Customer name',
                'required' => true,
            ],
            'email' => [
                'type' => 'string',
                'description' => 'Customer email',
                'required' => true,
            ],
            'phone' => [
                'type' => 'string',
                'description' => 'Customer phone number',
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
                'phone' => ['type' => 'string'],
            ],
        ];
    }

    public function execute(array $args): array
    {
        $customer = Customer::create([
            'name' => $args['name'],
            'email' => $args['email'],
            'phone' => $args['phone'] ?? null,
        ]);

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
        ];
    }
}