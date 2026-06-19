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
             WHERE activo = 1 ORDER BY fijo DESC, orden ASC, creado_en DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    public function obtenerFijas(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nombre, slug, imagen FROM categorias
             WHERE activo = 1 AND fijo = 1 ORDER BY orden ASC, creado_en DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    public function obtenerTodas(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nombre, slug, imagen, activo, fijo, orden, creado_en FROM categorias
             ORDER BY fijo DESC, orden ASC, creado_en DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    public function obtenerPorSlug(string $slug): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, slug, imagen FROM categorias WHERE slug = ? AND activo = 1'
        );
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public function obtenerPorId(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, slug, imagen, activo, fijo, orden FROM categorias WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function crear(string $nombre, string $slug, ?string $imagen, int $fijo = 0, ?int $orden = null): int
    {
        if ($orden === null) {
            // Obtener el máximo orden actual para poner la nueva al final
            $maxOrden = (int) $this->db->query('SELECT COALESCE(MAX(orden),0) FROM categorias')->fetchColumn();
            $orden = $maxOrden + 1;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO categorias (nombre, slug, imagen, fijo, orden) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$nombre, $slug, $imagen, $fijo, $orden]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, string $nombre, string $slug, ?string $imagen, int $activo, int $fijo = 0, ?int $orden = null): bool
    {
        if ($orden !== null) {
            if ($imagen !== null) {
                $stmt = $this->db->prepare(
                    'UPDATE categorias SET nombre=?, slug=?, imagen=?, activo=?, fijo=?, orden=? WHERE id=?'
                );
                return $stmt->execute([$nombre, $slug, $imagen, $activo, $fijo, $orden, $id]);
            }
            $stmt = $this->db->prepare(
                'UPDATE categorias SET nombre=?, slug=?, activo=?, fijo=?, orden=? WHERE id=?'
            );
            return $stmt->execute([$nombre, $slug, $activo, $fijo, $orden, $id]);
        }

        if ($imagen !== null) {
            $stmt = $this->db->prepare(
                'UPDATE categorias SET nombre=?, slug=?, imagen=?, activo=?, fijo=? WHERE id=?'
            );
            return $stmt->execute([$nombre, $slug, $imagen, $activo, $fijo, $id]);
        }
        $stmt = $this->db->prepare(
            'UPDATE categorias SET nombre=?, slug=?, activo=?, fijo=? WHERE id=?'
        );
        return $stmt->execute([$nombre, $slug, $activo, $fijo, $id]);
    }

    /**
     * Normaliza el orden de las categorías para que sean consecutivos y sin duplicados
     */
    public function normalizarOrdenes(): void
    {
        // Normalizar para fijas (fijo = 1)
        $stmt = $this->db->query('SELECT id FROM categorias WHERE fijo = 1 ORDER BY orden ASC, creado_en DESC, id DESC');
        $fijas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $upd = $this->db->prepare('UPDATE categorias SET orden = ? WHERE id = ?');
        foreach ($fijas as $index => $id) {
            $upd->execute([$index + 1, $id]);
        }

        // Normalizar para normales (fijo = 0)
        $stmt = $this->db->query('SELECT id FROM categorias WHERE fijo = 0 ORDER BY orden ASC, creado_en DESC, id DESC');
        $normales = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($normales as $index => $id) {
            $upd->execute([$index + 1, $id]);
        }
    }

    /**
     * Intercambia el orden de dos categorías (para mover arriba/abajo)
     */
    public function intercambiarOrden(int $idA, int $idB): void
    {
        $this->normalizarOrdenes();

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
