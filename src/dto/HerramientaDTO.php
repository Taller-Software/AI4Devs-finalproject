<?php
namespace App\DTO;

class HerramientaDTO {
    public function __construct(
        public readonly int $id = 0,
        public readonly string $nombre = '',
        public readonly string $codigo = '',
        public readonly bool $activo = true,
        public readonly string $ubicacion_actual = '',
        public readonly ?int $ubicacion_id = null,
        public readonly ?string $operario_actual = null,
        public readonly ?string $operario_uuid = null,
        public readonly ?string $fecha_inicio = null,
        public readonly ?string $fecha_fin = null
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            id: $data['id'] ?? 0,
            nombre: $data['nombre'] ?? '',
            codigo: $data['codigo'] ?? '',
            activo: $data['activo'] ?? true,
            ubicacion_actual: $data['ubicacion_actual'] ?? '',
            ubicacion_id: $data['ubicacion_id'] ?? null,
            operario_actual: $data['operario_actual'] ?? null,
            operario_uuid: $data['operario_uuid'] ?? null,
            fecha_inicio: $data['fecha_inicio'] ?? null,
            fecha_fin: $data['fecha_fin'] ?? null
        );
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'codigo' => $this->codigo,
            'activo' => $this->activo,
            'ubicacion_actual' => $this->ubicacion_actual,
            'ubicacion_id' => $this->ubicacion_id,
            'operario_actual' => $this->operario_actual,
            'operario_uuid' => $this->operario_uuid,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin
        ];
    }
}