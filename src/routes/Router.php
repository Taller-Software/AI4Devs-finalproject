<?php
namespace App\Routes;

use App\Api\AuthEndpoint;
use App\Api\HerramientasEndpoint;
use App\Api\DashboardEndpoint;
use App\Api\HistoricoEndpoint;
use App\Middlewares\SessionMiddleware;
use App\Middlewares\CsrfMiddleware;
use App\Middlewares\SecurityHeadersMiddleware;
use App\Middlewares\AuthMiddleware;
use App\DTO\ResponseDTO;
use App\Utils\Logger;

class Router {
    private static function json(ResponseDTO $response): void {
        Logger::info("Inicio del método", "Router::json");
        Logger::info("Status code: " . $response->statusCode, "Router::json");
        Logger::info("Respuesta DTO: " . json_encode($response->toArray()), "Router::json");
        
        http_response_code($response->statusCode);
        header('Content-Type: application/json');
        
        $jsonData = json_encode($response->toArray());
        if ($jsonData === false) {
            Logger::error("Error al codificar JSON: " . json_last_error_msg(), "Router::json");
            Logger::error("Datos que causaron el error: " . print_r($response->toArray(), true), "Router::json");
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error interno del servidor al procesar la respuesta'
            ]);
            return;
        }
        
        Logger::debug("JSON final a enviar: " . $jsonData, "Router::json");
        echo $jsonData;
    }

    public static function handleRequest(string $method, string $uri): void {
        // Extraer la ruta base de la URI
        $path = parse_url($uri, PHP_URL_PATH);
        $path = rtrim($path, '/');

        // Leer el body JSON si es POST
        $jsonBody = null;
        if ($method === 'POST') {
            Logger::info("Método POST detectado", "Router");
            $rawBody = file_get_contents('php://input');
            Logger::debug("Raw body recibido: " . $rawBody, "Router");
            Logger::debug("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'no definido'), "Router");
            
            // Solo decodificar si hay contenido
            if (!empty($rawBody)) {
                $jsonBody = json_decode($rawBody, true);
                if ($jsonBody === null && json_last_error() !== JSON_ERROR_NONE) {
                    Logger::error("Error decodificando JSON: " . json_last_error_msg(), "Router");
                    self::json(new ResponseDTO(false, "Invalid JSON payload: " . json_last_error_msg(), null, 400));
                    return;
                }
                Logger::debug("JSON decodificado exitosamente: " . json_encode($jsonBody), "Router");
            } else {
                Logger::debug("Body vacío - permitido para algunas rutas como logout", "Router");
                $jsonBody = []; // Array vacío en lugar de null
            }
        }

        // Inicializar la base de datos si es necesario
        try {
            $initializer = new \App\Utils\ProjectInitializer();
            $initializer->initializeProject();
        } catch (\Exception $e) {
            error_log('Error al inicializar el proyecto: ' . $e->getMessage());
        }

        // Aplicar middlewares de seguridad
        (new SecurityHeadersMiddleware())->handle();
        
        // No aplicar session y auth middleware en rutas de login y logout
        if (!self::isLoginRoute($path) && !self::isLogoutRoute($path)) {
            (new SessionMiddleware())->handle();
            $authResponse = AuthMiddleware::verificarSesion();
            if ($authResponse !== null) {
                self::json($authResponse);
                return;
            }
        } elseif (self::isLogoutRoute($path)) {
            // Para logout, solo iniciar sesión sin verificar autenticación
            (new SessionMiddleware())->handle();
        }

        // Aplicar CSRF middleware en POST requests (excepto en rutas de login y logout)
        if ($method === 'POST' && !self::isLoginRoute($path) && !self::isLogoutRoute($path)) {
            (new CsrfMiddleware())->handle();
        }

        // Extraer ID de la URL si existe
        preg_match('/\/api\/herramientas\/(\d+)/', $path, $matches);
        $id = $matches[1] ?? null;
        
        if (!$id) {
            preg_match('/\/AI4Devs-finalproject\/api\/herramientas\/(\d+)/', $path, $matches);
            $id = $matches[1] ?? null;
        }

        try {
            switch (true) {
                case $method === 'POST' && ($path === '/api/login/send-code' || $path === '/AI4Devs-finalproject/api/login/send-code'):
                    if ($jsonBody === null) {
                        self::json(new ResponseDTO(false, "Invalid JSON payload", null, 400));
                        return;
                    }
                    self::json((new AuthEndpoint())->sendCode($jsonBody));
                    break;

                case $method === 'POST' && ($path === '/api/login/validate-code' || $path === '/AI4Devs-finalproject/api/login/validate-code'):
                    self::json((new AuthEndpoint())->validateCode());
                    break;

                case $method === 'GET' && ($path === '/api/login/check-session' || $path === '/AI4Devs-finalproject/api/login/check-session'):
                    self::json((new AuthEndpoint())->checkSession());
                    break;

                case $method === 'GET' && ($path === '/api/csrf-token' || $path === '/AI4Devs-finalproject/api/csrf-token'):
                    self::json((new AuthEndpoint())->getCsrfToken());
                    break;

                case $method === 'POST' && ($path === '/api/login/logout' || $path === '/AI4Devs-finalproject/api/login/logout'):
                    self::json((new AuthEndpoint())->logout());
                    break;

                case $method === 'GET' && ($path === '/api/herramientas' || $path === '/AI4Devs-finalproject/api/herramientas'):
                    self::json((new HerramientasEndpoint())->index());
                    break;

                case $method === 'GET' && ($path === "/api/herramientas/$id/estado" || $path === "/AI4Devs-finalproject/api/herramientas/$id/estado"):
                    self::json((new HerramientasEndpoint())->getEstado($id));
                    break;

                case $method === 'POST' && ($path === "/api/herramientas/$id/usar" || $path === "/AI4Devs-finalproject/api/herramientas/$id/usar"):
                    self::json((new HerramientasEndpoint())->usar($id));
                    break;

                case $method === 'POST' && ($path === "/api/herramientas/$id/dejar" || $path === "/AI4Devs-finalproject/api/herramientas/$id/dejar"):
                    self::json((new HerramientasEndpoint())->dejar($id));
                    break;

                case $method === 'GET' && ($path === "/api/herramientas/$id/historial" || $path === "/AI4Devs-finalproject/api/herramientas/$id/historial"):
                    self::json((new HerramientasEndpoint())->historial($id));
                    break;

                case $method === 'GET' && ($path === '/api/dashboard' || $path === '/AI4Devs-finalproject/api/dashboard'):
                    self::json((new DashboardEndpoint())->index());
                    break;

                case $method === 'GET' && ($path === '/api/historico' || $path === '/AI4Devs-finalproject/api/historico'):
                    self::json((new HistoricoEndpoint())->index());
                    break;

                case $method === 'GET' && ($path === '/api/ubicaciones' || $path === '/AI4Devs-finalproject/api/ubicaciones'):
                    self::json((new HerramientasEndpoint())->getUbicaciones());
                    break;

                default:
                    self::json(new ResponseDTO(false, "Ruta no encontrada", null, 404));
            }
        } catch (\Exception $e) {
            self::json(new ResponseDTO(false, "Error interno del servidor: " . $e->getMessage(), null, 500));
        }
    }

    private static function isLoginRoute(string $path): bool {
        $loginPatterns = [
            '/api/login/send-code',
            '/AI4Devs-finalproject/api/login/send-code',
            '/api/login/validate-code',
            '/AI4Devs-finalproject/api/login/validate-code',
            '/api/login/check-session',
            '/AI4Devs-finalproject/api/login/check-session',
            '/api/csrf-token',
            '/AI4Devs-finalproject/api/csrf-token'
        ];
        
        foreach ($loginPatterns as $pattern) {
            if (strpos($path, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }

    private static function isLogoutRoute(string $path): bool {
        $logoutPatterns = [
            '/api/login/logout',
            '/AI4Devs-finalproject/api/login/logout'
        ];
        
        foreach ($logoutPatterns as $pattern) {
            if (strpos($path, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
}