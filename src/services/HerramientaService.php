<?php
namespace App\Services;

use App\DTO\ResponseDTO;
use App\DTO\HerramientaDTO;
use PDO;
use App\Services\DatabaseService;

class HerramientaService {

    public function getHerramientas(): ResponseDTO {
        try {
            $result = DatabaseService::executeQuery("
                SELECT h.*, 
                       u.nombre as ubicacion_actual,
                       o.nombre as operario_actual,
                       o.uuid as operario_uuid,
                       m.fecha_inicio,
                       m.fecha_fin,
                       m.id as movimiento_id,
                       m.ubicacion_id
                FROM herramientas h
                LEFT JOIN movimientos_herramienta m ON h.id = m.herramienta_id 
                    AND m.id = (
                        SELECT id 
                        FROM movimientos_herramienta 
                        WHERE herramienta_id = h.id 
                        ORDER BY dh_created DESC 
                        LIMIT 1
                    )
                LEFT JOIN ubicaciones u ON m.ubicacion_id = u.id
                LEFT JOIN usuarios o ON m.operario_uuid = o.uuid
                WHERE h.activo = 1
                ORDER BY h.nombre
            ");

            $herramientas = array_map(
                fn($row) => HerramientaDTO::fromArray($row),
                $result
            );

            return new ResponseDTO(true, "Herramientas recuperadas con éxito", $herramientas);
        } catch (\Exception $e) {
            return new ResponseDTO(false, "Error al recuperar herramientas: " . $e->getMessage(), null, 500);
        }
    }

    public function getEstadoHerramienta(int $id): ResponseDTO {
        try {
            $result = DatabaseService::executeQuery(
                "SELECT h.*,
                       u.nombre as ubicacion_actual,
                       o.nombre as operario_actual,
                       o.uuid as operario_uuid,
                       m.fecha_inicio,
                       m.fecha_fin,
                       m.ubicacion_id
                FROM herramientas h
                LEFT JOIN movimientos_herramienta m ON h.id = m.herramienta_id
                LEFT JOIN ubicaciones u ON m.ubicacion_id = u.id
                LEFT JOIN usuarios o ON m.operario_uuid = o.uuid
                WHERE h.id = ? AND h.activo = 1
                ORDER BY m.dh_created DESC
                LIMIT 1",
                [$id]
            );
            
            $herramienta = !empty($result) ? $result[0] : null;

            if (!$herramienta) {
                return new ResponseDTO(false, "Herramienta no encontrada", null, 404);
            }

            return new ResponseDTO(
                true,
                "Estado de herramienta recuperado con éxito",
                HerramientaDTO::fromArray($herramienta)
            );
        } catch (\Exception $e) {
            return new ResponseDTO(false, "Error al recuperar estado: " . $e->getMessage(), null, 500);
        }
    }

    public function usarHerramienta(int $id, int $ubicacionId, ?string $fechaFin): ResponseDTO {
        try {
            // Obtener UUID del operario de la sesión actual (seguridad)
            $sessionUser = \App\Utils\SessionManager::getSessionUser();
            if (!$sessionUser) {
                return new ResponseDTO(false, "No hay sesión activa", null, 401);
            }
            $operarioUuid = $sessionUser['uuid'];

            // Verificar si la herramienta está en uso (tiene operario asignado y no ha finalizado)
            $result = DatabaseService::executeQuery(
                "SELECT m.*, o.nombre as operario_nombre, u.nombre as ubicacion_nombre
                FROM movimientos_herramienta m
                JOIN usuarios o ON m.operario_uuid = o.uuid
                JOIN ubicaciones u ON m.ubicacion_id = u.id
                WHERE m.herramienta_id = ? 
                  AND m.operario_uuid IS NOT NULL 
                LIMIT 1",
                [$id]
            );

            $usoActual = !empty($result) ? $result[0] : null;

            if ($usoActual) {
                return new ResponseDTO(
                    false,
                    "La herramienta está siendo utilizada por {$usoActual['operario_nombre']} en {$usoActual['ubicacion_nombre']}",
                    null,
                    409
                );
            }

            // Registrar nuevo uso (fecha_inicio usa CURRENT_TIMESTAMP automáticamente)
            DatabaseService::executeStatement(
                "INSERT INTO movimientos_herramienta 
                (herramienta_id, operario_uuid, ubicacion_id, fecha_inicio, fecha_fin)
                VALUES (?, ?, ?, CURRENT_TIMESTAMP, ?)",
                [$id, $operarioUuid, $ubicacionId, $fechaFin]
            );
            return new ResponseDTO(true, "Herramienta registrada para uso correctamente");
        } catch (\Exception $e) {
            return new ResponseDTO(false, "Error al registrar uso: " . $e->getMessage(), null, 500);
        }
    }

    public function dejarHerramienta(int $id, int $ubicacionId): ResponseDTO {
        try {
            // 1. Cerrar el movimiento actual (finalizar uso por el operario)
            // Obtener el ID del último movimiento activo
            DatabaseService::executeStatement(
                "UPDATE movimientos_herramienta
                SET fecha_fin = CURRENT_TIMESTAMP
                WHERE id = (
                    SELECT id FROM (
                        SELECT id FROM movimientos_herramienta 
                        WHERE herramienta_id = ? 
                        ORDER BY dh_created DESC 
                        LIMIT 1
                    ) AS ultimo_movimiento
                )",
                [$id]
            );
            
            // 2. Crear nuevo registro sin operario (operario_uuid = NULL indica que está disponible)
            DatabaseService::executeStatement(
                "INSERT INTO movimientos_herramienta 
                (herramienta_id, operario_uuid, ubicacion_id, fecha_inicio)
                VALUES (?, NULL, ?, CURRENT_TIMESTAMP)",
                [$id, $ubicacionId]
            );
            
            return new ResponseDTO(true, "Herramienta devuelta correctamente");
        } catch (\Exception $e) {
            return new ResponseDTO(false, "Error al devolver herramienta: " . $e->getMessage(), null, 500);
        }
    }

    public function getHistorial(int $id): ResponseDTO {
        try {
            $historial = DatabaseService::executeQuery(
                "SELECT m.*,
                       h.nombre as herramienta_nombre,
                       u.nombre as ubicacion_nombre,
                       o.nombre as operario_nombre
                FROM movimientos_herramienta m
                JOIN herramientas h ON m.herramienta_id = h.id
                LEFT JOIN ubicaciones u ON m.ubicacion_id = u.id
                LEFT JOIN usuarios o ON m.operario_uuid = o.uuid
                WHERE m.herramienta_id = ?
                ORDER BY m.fecha_inicio DESC",
                [$id]
            );

            return new ResponseDTO(true, "Historial recuperado con éxito", $historial);
        } catch (\Exception $e) {
            return new ResponseDTO(false, "Error al recuperar historial: " . $e->getMessage(), null, 500);
        }
    }
}