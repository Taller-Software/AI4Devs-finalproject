<?php
namespace App\Controllers;

use App\Services\HerramientaService;
use App\DTO\ResponseDTO;
use App\Utils\SessionManager;

class HerramientaController {
    private HerramientaService $herramientaService;

    public function __construct() {
        $this->herramientaService = new HerramientaService();
    }

    public function index(): ResponseDTO {
        return $this->herramientaService->getHerramientas();
    }

    public function getEstado(int $id): ResponseDTO {
        return $this->herramientaService->getEstadoHerramienta($id);
    }

    public function usar(int $id, array $data = []): ResponseDTO {
        try {
            error_log("[CONTROLLER] usar() iniciado - ID: $id, Data: " . json_encode($data));
            
            // Validar sesión del operario usando SessionManager
            error_log("[CONTROLLER] Obteniendo sessionUser...");
            $sessionUser = SessionManager::getSessionUser();
            error_log("[CONTROLLER] SessionUser: " . json_encode($sessionUser));
            
            if (!$sessionUser) {
                error_log("[CONTROLLER] ERROR: Sesión no válida");
                return new ResponseDTO(false, "Sesión no válida", null, 401);
            }

            // Obtener datos del array o de $_POST (compatibilidad)
            $ubicacionId = $data['ubicacion_id'] ?? filter_input(INPUT_POST, 'ubicacion_id', FILTER_VALIDATE_INT);
            $fechaFin = $data['fecha_fin'] ?? filter_input(INPUT_POST, 'fecha_fin', FILTER_SANITIZE_STRING);
            
            error_log("[CONTROLLER] ubicacionId: $ubicacionId, fechaFin: " . ($fechaFin ?? 'NULL'));

            if (!$ubicacionId) {
                error_log("[CONTROLLER] ERROR: Datos incompletos");
                return new ResponseDTO(false, "Datos incompletos", null, 400);
            }

            // El operario_uuid se obtiene de la sesión en el servicio (seguridad)
            error_log("[CONTROLLER] Llamando a usarHerramienta...");
            $result = $this->herramientaService->usarHerramienta(
                $id,
                $ubicacionId,
                $fechaFin
            );
            error_log("[CONTROLLER] Resultado: " . json_encode($result));
            return $result;
        } catch (\Exception $e) {
            error_log("[CONTROLLER] ERROR CAPTURADO: " . $e->getMessage());
            error_log("[CONTROLLER] Stack trace: " . $e->getTraceAsString());
            return new ResponseDTO(false, "Error en controlador: " . $e->getMessage(), null, 500);
        }
    }

    public function dejar(int $id, array $data = []): ResponseDTO {
        // Obtener datos del array o de $_POST (compatibilidad)
        $ubicacionId = $data['ubicacion_id'] ?? filter_input(INPUT_POST, 'ubicacion_id', FILTER_VALIDATE_INT);

        if (!$ubicacionId) {
            return new ResponseDTO(false, "Ubicación no especificada", null, 400);
        }

        return $this->herramientaService->dejarHerramienta($id, $ubicacionId);
    }

    public function historial(int $id): ResponseDTO {
        return $this->herramientaService->getHistorial($id);
    }
}