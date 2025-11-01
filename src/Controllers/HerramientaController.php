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
        // Validar sesión del operario usando SessionManager
        $sessionUser = SessionManager::getSessionUser();
        if (!$sessionUser) {
            return new ResponseDTO(false, "Sesión no válida", null, 401);
        }

        // Obtener datos del array o de $_POST (compatibilidad)
        $ubicacionId = $data['ubicacion_id'] ?? filter_input(INPUT_POST, 'ubicacion_id', FILTER_VALIDATE_INT);
        $fechaFin = $data['fecha_fin'] ?? filter_input(INPUT_POST, 'fecha_fin', FILTER_SANITIZE_STRING);

        if (!$ubicacionId) {
            return new ResponseDTO(false, "Datos incompletos", null, 400);
        }

        // El operario_uuid se obtiene de la sesión en el servicio (seguridad)
        return $this->herramientaService->usarHerramienta(
            $id,
            $ubicacionId,
            $fechaFin
        );
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