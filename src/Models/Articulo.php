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
             ORDER BY orden ASC, creado_en DESC, id DESC'
        );
        $stmt->execute([$categoriaId]);
        return $stmt->fetchAll();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query(
            'SELECT a.id, a.nombre, a.imagen, a.activo, a.creado_en, a.orden,
                    a.cuotas_sem_cant, a.cuotas_sem_monto,
                    a.cuotas_mes_cant, a.cuotas_mes_monto,
                    c.nombre AS categoria_nombre
             FROM articulos a
             JOIN categorias c ON c.id = a.categoria_id
             ORDER BY a.orden ASC, a.creado_en DESC, a.id DESC'
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
        ?int $cuotasMesCant, ?float $cuotasMesMonto,
        ?int $orden = null
    ): int {
        if ($orden === null) {
            // Obtener el máximo orden dentro de la misma categoría
            $stmt = $this->db->prepare('SELECT COALESCE(MAX(orden),0) FROM articulos WHERE categoria_id = ?');
            $stmt->execute([$categoriaId]);
            $maxOrden = (int) $stmt->fetchColumn();
            $orden = $maxOrden + 1;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO articulos
             (categoria_id, nombre, descripcion, imagen, precio_contado,
              cuotas_sem_cant, cuotas_sem_monto, cuotas_mes_cant, cuotas_mes_monto, orden)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $categoriaId, $nombre, $descripcion, $imagen, $precioContado,
            $cuotasSemCant, $cuotasSemMonto, $cuotasMesCant, $cuotasMesMonto,
            $orden
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(
        int $id, int $categoriaId, string $nombre, ?string $descripcion,
        ?string $imagen, ?float $precioContado,
        ?int $cuotasSemCant, ?float $cuotasSemMonto,
        ?int $cuotasMesCant, ?float $cuotasMesMonto,
        int $activo, ?int $orden = null
    ): bool {
        if ($orden !== null) {
            if ($imagen !== null) {
                $stmt = $this->db->prepare(
                    'UPDATE articulos SET categoria_id=?, nombre=?, descripcion=?, imagen=?,
                     precio_contado=?, cuotas_sem_cant=?, cuotas_sem_monto=?,
                     cuotas_mes_cant=?, cuotas_mes_monto=?, activo=?, orden=?
                     WHERE id=?'
                );
                return $stmt->execute([
                    $categoriaId, $nombre, $descripcion, $imagen, $precioContado,
                    $cuotasSemCant, $cuotasSemMonto, $cuotasMesCant, $cuotasMesMonto,
                    $activo, $orden, $id
                ]);
            }
            $stmt = $this->db->prepare(
                'UPDATE articulos SET categoria_id=?, nombre=?, descripcion=?,
                 precio_contado=?, cuotas_sem_cant=?, cuotas_sem_monto=?,
                 cuotas_mes_cant=?, cuotas_mes_monto=?, activo=?, orden=?
                 WHERE id=?'
            );
            return $stmt->execute([
                $categoriaId, $nombre, $descripcion, $precioContado,
                $cuotasSemCant, $cuotasSemMonto, $cuotasMesCant, $cuotasMesMonto,
                $activo, $orden, $id
            ]);
        }

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

    /**
     * Normaliza el orden de los artículos dentro de una categoría para que sean consecutivos
     */
    public function normalizarOrdenes(int $categoriaId): void
    {
        $stmt = $this->db->prepare('SELECT id FROM articulos WHERE categoria_id = ? ORDER BY orden ASC, creado_en DESC, id DESC');
        $stmt->execute([$categoriaId]);
        $articulos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $upd = $this->db->prepare('UPDATE articulos SET orden = ? WHERE id = ?');
        foreach ($articulos as $index => $id) {
            $upd->execute([$index + 1, $id]);
        }
    }

    /**
     * Intercambia el orden de dos artículos (mover arriba/abajo)
     */
    public function intercambiarOrden(int $idA, int $idB): void
    {
        // Obtener la categoría del artículo A para saber cuál normalizar
        $stmt = $this->db->prepare('SELECT categoria_id FROM articulos WHERE id = ?');
        $stmt->execute([$idA]);
        $categoriaId = (int)$stmt->fetchColumn();
        
        if ($categoriaId) {
            $this->normalizarOrdenes($categoriaId);
        }

        $stmt = $this->db->prepare('SELECT id, orden FROM articulos WHERE id IN (?,?)');
        $stmt->execute([$idA, $idB]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) < 2) return;

        $upd = $this->db->prepare('UPDATE articulos SET orden=? WHERE id=?');
        $upd->execute([$rows[1]['orden'], $rows[0]['id']]);
        $upd->execute([$rows[0]['orden'], $rows[1]['id']]);
    }

    /**
     * Asigna un número de orden directo a un artículo
     */
    public function actualizarOrden(int $id, int $orden): bool
    {
        $stmt = $this->db->prepare('UPDATE articulos SET orden=? WHERE id=?');
        return $stmt->execute([$orden, $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM articulos WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function contarTodos(int $categoriaId = 0, string $busqueda = ''): int
    {
        $where = '';
        $params = [];
        if ($categoriaId) { $where .= ' AND a.categoria_id = ?'; $params[] = $categoriaId; }
        if ($busqueda)    { $where .= ' AND a.nombre LIKE ?';    $params[] = "%$busqueda%"; }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM articulos a WHERE 1=1 $where"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function obtenerPaginados(int $limit, int $offset, int $categoriaId = 0, string $busqueda = ''): array
    {
        $where = '';
        $params = [];
        if ($categoriaId) { $where .= ' AND a.categoria_id = ?'; $params[] = $categoriaId; }
        if ($busqueda)    { $where .= ' AND a.nombre LIKE ?';    $params[] = "%$busqueda%"; }
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare(
            "SELECT a.id, a.nombre, a.imagen, a.activo, a.creado_en, a.orden,
                    a.cuotas_sem_cant, a.cuotas_sem_monto,
                    a.cuotas_mes_cant, a.cuotas_mes_monto,
                    c.nombre AS categoria_nombre
             FROM articulos a
             JOIN categorias c ON c.id = a.categoria_id
             WHERE 1=1 $where
             ORDER BY a.orden ASC, a.creado_en DESC, a.id DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
