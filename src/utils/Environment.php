<?php
namespace App\Utils;

class Environment {
    private static array $env = [];
    private static bool $loaded = false;

    public static function init(): void {
        self::loadEnv();
    }

    private static function loadEnv(): void {
        if (self::$loaded) {
            return;
        }

        $envFile = __DIR__ . '/../../.env';
        if (!file_exists($envFile)) {
            throw new \RuntimeException("El archivo .env no existe");
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Manejar valores vacíos
            if ($value === '""' || $value === "''") {
                $value = '';
            }
            // Quitar comillas si existen
            elseif (preg_match('/^"(.+)"$/', $value, $matches) || 
                    preg_match("/^'(.+)'$/", $value, $matches)) {
                $value = $matches[1];
            }

            self::$env[$name] = $value;
        }
        
        self::$loaded = true;
    }

    public static function get(string $key, $default = null) {
        if (!self::$loaded) {
            self::loadEnv();
        }
        return self::$env[$key] ?? $default;
    }

    public static function isProduction(): bool {
        return self::get('APP_ENV') === 'production';
    }

    public static function isDevelopment(): bool {
        $env = self::get('APP_ENV');
        return $env === 'development' || $env === 'test';
    }

    public static function isTest(): bool {
        return self::get('APP_ENV') === 'test';
    }
}