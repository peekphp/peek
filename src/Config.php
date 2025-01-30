<?php

namespace Peek;

class Config
{
    private static string $configPath;

    public static function init(): void
    {
        self::$configPath = getcwd().'/peek.json';
    }

    public static function exists(): bool
    {
        return file_exists(self::$configPath);
    }

    public static function getAllClients(): array
    {
        self::init();

        if (! self::exists()) {
            return [];
        }

        $config = json_decode(file_get_contents(self::$configPath), true);

        return $config['clients'] ?? [];
    }

    public static function setClientConfig(string $client, string $apiKey, string $url, string $model): void
    {
        self::init();

        $config = self::exists() ? json_decode(file_get_contents(self::$configPath), true) : [];

        if (! isset($config['clients'])) {
            $config['clients'] = [];
        }

        $config['clients'][$client] = [
            'api_key' => $apiKey,
            'url' => $url,
            'model' => $model,
        ];

        file_put_contents(self::$configPath, json_encode($config, JSON_PRETTY_PRINT));
    }
}
