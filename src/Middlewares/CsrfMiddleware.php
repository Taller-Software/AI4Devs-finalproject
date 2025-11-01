<?php

namespace App\Middlewares;

use App\Utils\SessionManager;

class CsrfMiddleware {
    public function handle(): void {
        // Skip CSRF check for GET requests
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return;
        }

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;
        
        // Debug logs
        error_log("[CSRF] Token recibido: " . ($token ?? 'NULL'));
        error_log("[CSRF] Headers disponibles: " . json_encode(array_keys($_SERVER)));

        if (!$token || !SessionManager::validateCsrfToken($token)) {
            error_log("[CSRF] Validación fallida - Token: " . ($token ?? 'NULL'));
            http_response_code(403);
            echo json_encode(['error' => 'Invalid CSRF token', 'debug' => ['token_received' => $token ? 'yes' : 'no']]);
            exit();
        }
    }

    public static function getToken(): string {
        return SessionManager::getCsrfToken() ?? '';
    }
}