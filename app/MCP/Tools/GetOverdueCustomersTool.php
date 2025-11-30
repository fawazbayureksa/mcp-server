<?php

namespace App\MCP\Tools;

use App\Models\Customer;
use Carbon\Carbon;

class GetOverdueCustomersTool extends Tool
{
    public function getName(): string
    {
        return 'get_overdue_customers';
    }

    public function getDescription(): string
    {
        return 'Get customers who haven\'t been contacted in the specified number of days';
    }

    public function getParameters(): array
    {
        return [
            'days' => [
                'type' => 'integer',
                'description' => 'Number of days since last contact',
                'default' => 30,
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
                    'name' => ['type' => 'string'],
                    'email' => ['type' => 'string'],
                    'phone' => ['type' => 'string'],
                     'days_since_last_contact' => ['type' => 'integer'],
                ],
            ],
        ];
    }

    public function execute(array $args): array
    {
        $days = $args['days'] ?? 30;
        $cutoffDate = Carbon::now()->subDays($days);

        $customers = Customer::where(function ($query) use ($cutoffDate) {
            $query->where('last_contacted_at', '<', $cutoffDate)
                  ->orWhereNull('last_contacted_at');
        })->where('created_at', '<', $cutoffDate)->get();

        return $customers->map(function ($customer) {
            $lastContact = $customer->last_contacted_at ? Carbon::parse($customer->last_contacted_at) : Carbon::parse($customer->created_at);
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'days_since_last_contact' => $lastContact->diffInDays(Carbon::now()),
            ];
        })->toArray();
    }
}