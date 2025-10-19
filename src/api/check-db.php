<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Services\DatabaseService;

header('Content-Type: application/json');

try {
    // Verificar si las tablas existen
    $tables = ['usuarios', 'ubicaciones', 'herramientas', 'movimientos_herramienta', 'codigos_login'];
    $allTablesExist = true;
    
    foreach ($tables as $table) {
        try {
            $result = DatabaseService::executeQuery("SHOW TABLES LIKE ?", [$table]);
            if (empty($result)) {
                $allTablesExist = false;
                break;
            }
        } catch (\Exception $e) {
            $allTablesExist = false;
            break;
        }
    }
    
    echo json_encode([
        'success' => $allTablesExist,
        'message' => $allTablesExist ? 'Base de datos inicializada correctamente' : 'Se requiere inicialización'
    ]);
    
} catch (\Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos'
    ]);
}