<?php
/**
 * Punto de entrada para las peticiones API
 */

// Configuración inicial
require_once __DIR__ . '/utils/Environment.php';
require_once __DIR__ . '/utils/Logger.php';

// Cargar variables de entorno
try {
    App\Utils\Environment::init();
    App\Utils\Logger::initialize();
} catch (\RuntimeException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de configuración: ' . $e->getMessage()
    ]);
    exit;
}

// Autoload de clases
// Autoloader para PHPMailer
spl_autoload_register(function ($class) {
    // Verificar si es una clase de PHPMailer
    $prefix = 'PHPMailer\\PHPMailer\\';
    $baseDir = __DIR__ . '/../lib/PHPMailer/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Obtener el nombre de la clase sin el namespace
    $relativeClass = substr($class, $len);
    
    // Crear la ruta del archivo
    $file = $baseDir . $relativeClass . '.php';

    // Si el archivo existe, cargarlo
    if (file_exists($file)) {
        require $file;
    }
});

// Autoloader para las clases del proyecto
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Inicializar el proyecto si es necesario (solo en localhost)
// En Railway, la estructura ya está en el código fuente
if (App\Utils\Environment::isDevelopment()) {
    try {
        $initializer = new \App\Utils\ProjectInitializer();
        $initializer->initializeProject();
    } catch (\Exception $e) {
        error_log('Error al inicializar el proyecto: ' . $e->getMessage());
    }
}

// Configurar headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Cargar el Router explícitamente (por si el autoloader falla)
require_once __DIR__ . '/routes/Router.php';

// Iniciar la aplicación
try {
    // Crear instancia del router y procesar la solicitud
    \App\Routes\Router::handleRequest($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (\Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => App\Utils\Environment::get('APP_DEBUG', false) 
            ? $e->getMessage() 
            : 'Error interno del servidor'
    ]);
}