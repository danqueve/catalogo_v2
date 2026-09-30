<?php

namespace Models;

use Config\Database;
use PDO;

class Categoria
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function obtenerActivas(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nombre, slug, imagen, fijo FROM categorias
             WHERE activo = 1 AND categoria_padre_id IS NULL
             ORDER BY fijo DESC, orden ASC, creado_en DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    public function obtenerFijas(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nombre, slug, imagen FROM categorias
             WHERE activo = 1 AND fijo = 1 AND categoria_padre_id IS NULL
             ORDER BY orden ASC, creado_en DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    /** Subcategorías activas de una categoría, para la página pública. */
    public function obtenerSubcategorias(int $categoriaPadreId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, slug, imagen FROM categorias
             WHERE categoria_padre_id = ? AND activo = 1
             ORDER BY orden ASC, creado_en DESC, id DESC'
        );
        $stmt->execute([$categoriaPadreId]);
        return $stmt->fetchAll();
    }

    /** Categorías de nivel superior, para elegir como "padre" al crear una subcategoría. */
    public function obtenerPrincipales(?int $excluirId = null): array
    {
        $sql = 'SELECT id, nombre FROM categorias WHERE categoria_padre_id IS NULL';
        $params = [];
        if ($excluirId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excluirId;
        }
        $sql .= ' ORDER BY nombre ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Todas las categorías activas (incluye subcategorías), para elegir el destino de una promoción. */
    public function obtenerActivasConSubcategorias(): array
    {
        $stmt = $this->db->query(
            'SELECT c.id, c.nombre, c.slug, c.categoria_padre_id, p.nombre AS padre_nombre
             FROM categorias c
             LEFT JOIN categorias p ON p.id = c.categoria_padre_id
             WHERE c.activo = 1
             ORDER BY COALESCE(p.nombre, c.nombre) ASC, c.categoria_padre_id IS NULL DESC, c.nombre ASC'
        );
        return $stmt->fetchAll();
    }

    /** Categorías donde se pueden cargar artículos: subcategorías, o categorías sin subcategorías. */
    public function obtenerAsignables(): array
    {
        $stmt = $this->db->query(
            'SELECT c.id, c.nombre, c.categoria_padre_id, p.nombre AS padre_nombre
             FROM categorias c
             LEFT JOIN categorias p ON p.id = c.categoria_padre_id
             WHERE c.categoria_padre_id IS NOT NULL
                OR NOT EXISTS (SELECT 1 FROM categorias h WHERE h.categoria_padre_id = c.id)
             ORDER BY COALESCE(p.nombre, c.nombre) ASC, c.categoria_padre_id IS NULL DESC, c.nombre ASC'
        );
        return $stmt->fetchAll();
    }

    public function obtenerTodas(): array
    {
        $stmt = $this->db->query(
            'SELECT c.id, c.nombre, c.slug, c.imagen, c.activo, c.fijo, c.orden, c.categoria_padre_id, c.creado_en,
                    (SELECT COUNT(*) FROM categorias h WHERE h.categoria_padre_id = c.id) AS subcategorias_count,
                    (SELECT COUNT(*) FROM articulos a WHERE a.categoria_id = c.id) AS articulos_count
             FROM categorias c
             ORDER BY c.fijo DESC, c.orden ASC, c.creado_en DESC, c.id DESC'
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorSlug(string $slug): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT c.id, c.nombre, c.slug, c.imagen, c.categoria_padre_id,
                    p.nombre AS padre_nombre, p.slug AS padre_slug
             FROM categorias c
             LEFT JOIN categorias p ON p.id = c.categoria_padre_id
             WHERE c.slug = ? AND c.activo = 1'
        );
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, slug, imagen, activo, fijo, orden, categoria_padre_id FROM categorias WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function crear(string $nombre, string $slug, ?string $imagen, int $fijo = 0, ?int $orden = null, ?int $categoriaPadreId = null): int
    {
        if ($orden === null) {
            // Obtener el máximo orden actual para poner la nueva al final
            $maxOrden = (int) $this->db->query('SELECT COALESCE(MAX(orden),0) FROM categorias')->fetchColumn();
            $orden = $maxOrden + 1;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO categorias (nombre, slug, imagen, fijo, orden, categoria_padre_id) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$nombre, $slug, $imagen, $fijo, $orden, $categoriaPadreId]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, string $nombre, string $slug, ?string $imagen, int $activo, int $fijo = 0, ?int $orden = null, ?int $categoriaPadreId = null): bool
    {
        $campos  = ['nombre = ?', 'slug = ?', 'activo = ?', 'fijo = ?', 'categoria_padre_id = ?'];
        $valores = [$nombre, $slug, $activo, $fijo, $categoriaPadreId];

        if ($imagen !== null) {
            $campos[]  = 'imagen = ?';
            $valores[] = $imagen;
        }
        if ($orden !== null) {
            $campos[]  = 'orden = ?';
            $valores[] = $orden;
        }
        $valores[] = $id;

        $stmt = $this->db->prepare('UPDATE categorias SET ' . implode(', ', $campos) . ' WHERE id = ?');
        return $stmt->execute($valores);
    }

    /**
     * Normaliza el orden de las categorías para que sean consecutivos y sin duplicados.
     * Opera sólo dentro de un mismo grupo: las de nivel superior (por separado fijas/normales),
     * o las subcategorías de un mismo padre — nunca mezcla grupos distintos.
     */
    public function normalizarOrdenes(?int $categoriaPadreId = null): void
    {
        $upd = $this->db->prepare('UPDATE categorias SET orden = ? WHERE id = ?');

        if ($categoriaPadreId !== null) {
            $stmt = $this->db->prepare(
                'SELECT id FROM categorias WHERE categoria_padre_id = ?
                 ORDER BY orden ASC, creado_en DESC, id DESC'
            );
            $stmt->execute([$categoriaPadreId]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $index => $id) {
                $upd->execute([$index + 1, $id]);
            }
            return;
        }

        // Nivel superior: normalizar fijas y normales por separado
        $stmt = $this->db->query(
            'SELECT id FROM categorias WHERE categoria_padre_id IS NULL AND fijo = 1
             ORDER BY orden ASC, creado_en DESC, id DESC'
        );
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $index => $id) {
            $upd->execute([$index + 1, $id]);
        }

        $stmt = $this->db->query(
            'SELECT id FROM categorias WHERE categoria_padre_id IS NULL AND fijo = 0
             ORDER BY orden ASC, creado_en DESC, id DESC'
        );
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $index => $id) {
            $upd->execute([$index + 1, $id]);
        }
    }

    /**
     * Intercambia el orden de dos categorías (para mover arriba/abajo).
     * Ambas deben pertenecer al mismo grupo (mismo padre, o ambas de nivel superior).
     */
    public function intercambiarOrden(int $idA, int $idB): void
    {
        $stmt = $this->db->prepare('SELECT categoria_padre_id FROM categorias WHERE id = ?');
        $stmt->execute([$idA]);
        $padreId = $stmt->fetchColumn();
        $padreId = ($padreId !== false && $padreId !== null) ? (int)$padreId : null;

        $this->normalizarOrdenes($padreId);

        $stmt = $this->db->prepare('SELECT id, orden FROM categorias WHERE id IN (?,?)');
        $stmt->execute([$idA, $idB]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) < 2) return;

        $upd = $this->db->prepare('UPDATE categorias SET orden=? WHERE id=?');
        $upd->execute([$rows[1]['orden'], $rows[0]['id']]);
        $upd->execute([$rows[0]['orden'], $rows[1]['id']]);
    }

    /**
     * Asigna un número de orden directo a una categoría
     */
    public function actualizarOrden(int $id, int $orden): bool
    {
        $stmt = $this->db->prepare('UPDATE categorias SET orden=? WHERE id=?');
        return $stmt->execute([$orden, $id]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM categorias WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function slugify(string $texto): string
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        $mapa = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
                 'ñ'=>'n','ü'=>'u','à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u'];
        $texto = strtr($texto, $mapa);
        $texto = preg_replace('/[^a-z0-9\s-]/', '', $texto);
        return trim(preg_replace('/[\s-]+/', '-', $texto), '-');
    }
}
