<?php

namespace Helpers;

class Auth
{
    public static function esAdmin(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    public static function requiereAdmin(): void
    {
        if (!self::esAdmin()) {
            header('Location: ' . BASE_URL . '/../admin/login.php');
            exit;
        }
    }

    public static function login(int $id, string $nombre): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_id']     = $id;
        $_SESSION['admin_nombre'] = $nombre;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function generarCsrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validarCsrf(string $token): bool
    {
        return isset($_SESSION['csrf_token']) &&
               hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function campoCSRF(): string
    {
        $token = self::generarCsrf();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
