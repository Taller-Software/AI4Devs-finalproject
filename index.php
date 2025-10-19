<?php
// Redirigir todas las solicitudes al directorio public
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Si la URI es la raíz, redirigir a public/
if ($uri === '/AI4Devs-finalproject/' || $uri === '/AI4Devs-finalproject') {
    header('Location: /AI4Devs-finalproject/public/');
    exit;
}

// Si la URI comienza con /AI4Devs-finalproject/api/, manejar como API
if (strpos($uri, '/AI4Devs-finalproject/api/') === 0) {
    require_once __DIR__ . '/src/index.php';
    exit;
}

// Para cualquier otra URI que comience con /AI4Devs-finalproject/, servir desde public/
if (strpos($uri, '/AI4Devs-finalproject/') === 0) {
    $file = __DIR__ . '/public' . str_replace('/AI4Devs-finalproject', '', $uri);
    
    if (file_exists($file)) {
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
            'gif' => 'image/gif'
        ];
        
        if (isset($mime_types[$ext])) {
            header('Content-Type: ' . $mime_types[$ext]);
        }
        
        readfile($file);
        exit;
    }
    
    // Si el archivo no existe y no es una API, servir index.html
    if (!file_exists($file) && strpos($uri, '/AI4Devs-finalproject/api/') !== 0) {
        readfile(__DIR__ . '/public/index.html');
        exit;
    }
}