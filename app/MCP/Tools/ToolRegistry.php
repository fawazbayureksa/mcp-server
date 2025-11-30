<?php

namespace App\MCP\Tools;

use Illuminate\Support\Facades\File;

class ToolRegistry
{
    private static array $tools = [];

    public static function register(Tool $tool): void
    {
        self::$tools[$tool->getName()] = $tool;
    }

    public static function getTool(string $name): ?Tool
    {
        return self::$tools[$name] ?? null;
    }

    public static function getAllTools(): array
    {
        return self::$tools;
    }

    public static function getToolSchemas(): array
    {
        return array_map(function(Tool $tool) {
            return $tool->getToolSchema();
        }, self::$tools);
    }

    public static function autoDiscover(): void
    {
        $toolFiles = File::files(app_path('MCP/Tools'));

        foreach ($toolFiles as $file) {
            $className = 'App\\MCP\\Tools\\' . $file->getFilenameWithoutExtension();

            if (class_exists($className) && is_subclass_of($className, Tool::class)) {
                $tool = new $className();
                self::register($tool);
            }
        }
    }
}