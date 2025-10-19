<?php
/**
 * Router principal para Railway
 * Railway sirve desde la raíz sin subdirectorios
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Si es la raíz, servir index.html
if ($uri === '/' || $uri === '') {
    readfile(__DIR__ . '/public/index.html');
    exit;
}

// Si es una petición a la API
if (strpos($uri, '/api/') === 0) {
    require_once __DIR__ . '/src/index.php';
    exit;
}

// Intentar servir archivo estático desde public/
$file = __DIR__ . '/public' . $uri;

if (file_exists($file) && is_file($file)) {
    // Detectar el tipo MIME correcto
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $mime_types = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon'
    ];
    
    if (isset($mime_types[$ext])) {
        header('Content-Type: ' . $mime_types[$ext]);
    }
    
    readfile($file);
    exit;
}

// Si no se encuentra, servir index.html (para SPA routing)
readfile(__DIR__ . '/public/index.html');
exit;
