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
             WHERE activo = 1 ORDER BY fijo DESC, creado_en DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    public function obtenerFijas(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nombre, slug, imagen FROM categorias
             WHERE activo = 1 AND fijo = 1 ORDER BY creado_en DESC, id DESC'
        );
        return $stmt->fetchAll();
    }

    public function obtenerTodas(): array
    {
        $stmt = $this->db->query(
            'SELECT id, nombre, slug, imagen, activo, fijo, creado_en FROM categorias
             ORDER BY fijo DESC, creado_en DESC, id DESC'
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
            'SELECT id, nombre, slug, imagen, activo FROM categorias WHERE id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function crear(string $nombre, string $slug, ?string $imagen, int $fijo = 0): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO categorias (nombre, slug, imagen, fijo) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$nombre, $slug, $imagen, $fijo]);
        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, string $nombre, string $slug, ?string $imagen, int $activo, int $fijo = 0): bool
    {
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
