<?php
namespace App\Api;

use App\Controllers\HerramientaController;
use App\DTO\ResponseDTO;
use App\Utils\Validator;
use App\Utils\SessionManager;

class HerramientasEndpoint {
    private $controller;

    public function __construct() {
        $this->controller = new HerramientaController();
    }

    public function index(): ResponseDTO {
        try {
            return $this->controller->index();
        } catch (\Exception $e) {
            return new ResponseDTO(false, "Error al obtener las herramientas: " . $e->getMessage(), null, 500);
        }
    }

    public function getEstado(?int $id): ResponseDTO {
        try {
            if (!$id || !Validator::validateId($id)) {
                return new ResponseDTO(false, "ID de herramienta inválido", null, 400);
            }
            return $this->controller->getEstado($id);
        } catch (\Exception $e) {
            return new ResponseDTO(false, "Error al obtener el estado: " . $e->getMessage(), null, 500);
        }
    }

    public function usar(?int $id): ResponseDTO {
        try {
            error_log("[ENDPOINT] usar() llamado con ID: " . ($id ?? 'NULL'));
            
            if (!$id || !Validator::validateId($id)) {
                error_log("[ENDPOINT] ID inválido: " . ($id ?? 'NULL'));
                return new ResponseDTO(false, "ID de herramienta inválido", null, 400);
            }

            // Leer datos del body JSON
            error_log("[ENDPOINT] Leyendo body JSON...");
            $jsonData = json_decode(file_get_contents('php://input'), true);
            error_log("[ENDPOINT] JSON Data: " . json_encode($jsonData));
            
            if (!$jsonData) {
                error_log("[ENDPOINT] JSON inválido o vacío");
                return new ResponseDTO(false, "Datos inválidos", null, 400);
            }

            $ubicacion_id = $jsonData['ubicacion_id'] ?? null;
            $fecha_fin = $jsonData['fecha_fin'] ?? null;

            // Validar campos requeridos
            if (empty($ubicacion_id)) {
                error_log("[ENDPOINT] ubicacion_id vacío");
                return new ResponseDTO(false, "El campo ubicacion_id es obligatorio", null, 400);
            }

            // Validar ubicación
            if (!Validator::validateId($ubicacion_id)) {
                error_log("[ENDPOINT] ubicacion_id inválido: $ubicacion_id");
                return new ResponseDTO(false, "ID de ubicación inválido", null, 400);
            }

            // Validar fecha fin si se proporciona
            if (!empty($fecha_fin) && !Validator::validateDate($fecha_fin)) {
                error_log("[ENDPOINT] fecha_fin inválida: $fecha_fin");
                return new ResponseDTO(false, "La fecha fin proporcionada no es válida", null, 400);
            }

            // Preparar los datos para el controlador
            $data = [
                'ubicacion_id' => $ubicacion_id,
                'fecha_fin' => $fecha_fin,
                'operario_uuid' => $jsonData['operario_uuid'] ?? null
            ];

            error_log("[ENDPOINT] Llamando a controller->usar() con data: " . json_encode($data));
            return $this->controller->usar($id, $data);
        } catch (\Exception $e) {
            error_log("[ENDPOINT] ERROR capturado: " . $e->getMessage());
            error_log("[ENDPOINT] Stack trace: " . $e->getTraceAsString());
            return new ResponseDTO(false, "Error al usar la herramienta: " . $e->getMessage(), null, 500);
        }
    }

    public function dejar(?int $id): ResponseDTO {
        try {
            if (!$id || !Validator::validateId($id)) {
                return new ResponseDTO(false, "ID de herramienta inválido", null, 400);
            }

            // Leer datos del body JSON
            $jsonData = json_decode(file_get_contents('php://input'), true);
            
            if (!$jsonData) {
                return new ResponseDTO(false, "Datos inválidos", null, 400);
            }

            $ubicacion_id = $jsonData['ubicacion_id'] ?? null;
            
            if (empty($ubicacion_id) || !Validator::validateId($ubicacion_id)) {
                return new ResponseDTO(false, "ID de ubicación inválido o no proporcionado", null, 400);
            }

            // Preparar los datos para el controlador
            $data = [
                'ubicacion_id' => $ubicacion_id
            ];

            return $this->controller->dejar($id, $data);
        } catch (\Exception $e) {
            return new ResponseDTO(false, "Error al dejar la herramienta: " . $e->getMessage(), null, 500);
        }
    }

    public function historial(?int $id): ResponseDTO {
        try {
            if (!$id || !Validator::validateId($id)) {
                return new ResponseDTO(false, "ID de herramienta inválido", null, 400);
            }
            return $this->controller->historial($id);
        } catch (\Exception $e) {
            return new ResponseDTO(false, "Error al obtener el historial: " . $e->getMessage(), null, 500);
        }
    }

    public function getUbicaciones(): ResponseDTO {
        try {
            $ubicaciones = \App\Services\DatabaseService::executeQuery(
                "SELECT id, nombre FROM ubicaciones WHERE activo = 1 ORDER BY nombre"
            );
            return new ResponseDTO(true, "Ubicaciones obtenidas correctamente", $ubicaciones);
        } catch (\Exception $e) {
            return new ResponseDTO(false, "Error al obtener ubicaciones: " . $e->getMessage(), null, 500);
        }
    }
}