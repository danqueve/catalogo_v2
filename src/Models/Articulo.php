<?php

namespace Models;

use Config\Database;
use PDO;

class Articulo
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function obtenerPorCategoria(int $categoriaId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, descripcion, imagen,
                    precio_contado,
                    cuotas_sem_cant, cuotas_sem_monto,
                    cuotas_mes_cant, cuotas_mes_monto
             FROM articulos
             WHERE categoria_id = ? AND activo = 1
             ORDER BY creado_en DESC, id DESC'
        );
        $stmt->execute([$categoriaId]);
        return $stmt->fetchAll();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            'SELECT a.id, a.nombre, a.imagen, a.activo, a.creado_en,
                    a.cuotas_sem_cant, a.cuotas_sem_monto,
                    a.cuotas_mes_cant, a.cuotas_mes_monto,
                    c.nombre AS categoria_nombre
             FROM articulos a
             JOIN categorias c ON c.id = a.categoria_id
             ORDER BY a.creado_en DESC, a.id DESC'
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, c.nombre AS categoria_nombre, c.slug AS categoria_slug
             FROM articulos a
             JOIN categorias c ON c.id = a.categoria_id
             WHERE a.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function crear(
        int $categoriaId, string $nombre, ?string $descripcion, string $imagen,
        ?float $precioContado,
        ?int $cuotasSemCant, ?float $cuotasSemMonto,
        ?int $cuotasMesCant, ?float $cuotasMesMonto
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO articulos
             (categoria_id, nombre, descripcion, imagen, precio_contado,
              cuotas_sem_cant, cuotas_sem_monto, cuotas_mes_cant, cuotas_mes_monto)
             VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $categoriaId, $nombre, $descripcion, $imagen, $precioContado,
            $cuotasSemCant, $cuotasSemMonto, $cuotasMesCant, $cuotasMesMonto
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(
        int $id, int $categoriaId, string $nombre, ?string $descripcion,
        ?string $imagen, ?float $precioContado,
        ?int $cuotasSemCant, ?float $cuotasSemMonto,
        ?int $cuotasMesCant, ?float $cuotasMesMonto,
        int $activo
    ): bool {
        if ($imagen !== null) {
            $stmt = $this->db->prepare(
                'UPDATE articulos SET categoria_id=?, nombre=?, descripcion=?, imagen=?,
                 precio_contado=?, cuotas_sem_cant=?, cuotas_sem_monto=?,
                 cuotas_mes_cant=?, cuotas_mes_monto=?, activo=?
                 WHERE id=?'
            );
            return $stmt->execute([
                $categoriaId, $nombre, $descripcion, $imagen, $precioContado,
                $cuotasSemCant, $cuotasSemMonto, $cuotasMesCant, $cuotasMesMonto,
                $activo, $id
            ]);
        }
        $stmt = $this->db->prepare(
            'UPDATE articulos SET categoria_id=?, nombre=?, descripcion=?,
             precio_contado=?, cuotas_sem_cant=?, cuotas_sem_monto=?,
             cuotas_mes_cant=?, cuotas_mes_monto=?, activo=?
             WHERE id=?'
        );
        return $stmt->execute([
            $categoriaId, $nombre, $descripcion, $precioContado,
            $cuotasSemCant, $cuotasSemMonto, $cuotasMesCant, $cuotasMesMonto,
            $activo, $id
        ]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM articulos WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
