<?php

namespace App\MCP\Tools;

abstract class Tool
{
    abstract public function getName(): string;

    abstract public function getDescription(): string;

    abstract public function getParameters(): array;

    abstract public function getReturns(): array;

    abstract public function execute(array $args): array;

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'parameters' => $this->getParameters(),
            'returns' => $this->getReturns(),
        ];
    }

    public function getSchema(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'parameters' => [
                    'type' => 'object',
                    'properties' => $this->getParameters(),
                    'required' => array_keys(array_filter($this->getParameters(), function($param) {
                        return isset($param['required']) && $param['required'];
                    })),
                ],
            ],
        ];
    }

    // New OpenAI tools format
    public function getToolSchema(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'parameters' => [
                    'type' => 'object',
                    'properties' => $this->getParameters(),
                    'required' => array_keys(array_filter($this->getParameters(), function($param) {
                        return isset($param['required']) && $param['required'];
                    })),
                ],
            ],
        ];
    }
}