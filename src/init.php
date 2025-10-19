<?php
require_once __DIR__ . '/bootstrap.php';
use App\Services\DatabaseService;

try {
    // Intentar inicializar la base de datos
    $schemaFile = __DIR__ . '/../db/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception('Archivo schema.sql no encontrado');
    }
    
    // Ejecutar el script de creación de base de datos
    $schema = file_get_contents($schemaFile);
    DatabaseService::executeScript($schema, true);
    
    // Ejecutar script de datos iniciales si existe
    $dataFile = __DIR__ . '/../db/data.sql';
    if (file_exists($dataFile)) {
        $data = file_get_contents($dataFile);
        DatabaseService::executeScript($data);
    }
    
    // Si llegamos aquí, la inicialización fue exitosa
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Base de datos inicializada correctamente'
    ]);
    
    // Redirigir a la página principal
    // Detectar si estamos en localhost o en producción
    $baseUrl = (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false) 
        ? '/AI4Devs-finalproject/public/' 
        : '/';
    header('Location: ' . $baseUrl);
    exit;
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error al inicializar la base de datos: ' . $e->getMessage()
    ]);
}