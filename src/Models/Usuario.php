<?php

namespace Models;

use Config\Database;
use PDO;

class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
    }

    public function buscarPorUsuario(string $usuario): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id, usuario, password_hash, nombre FROM usuarios WHERE usuario = ?'
        );
        $stmt->execute([$usuario]);
        return $stmt->fetch();
    }
}
