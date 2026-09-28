<?php
require_once dirname(__DIR__) . '/config/config.php';

// Las páginas PHP deben revalidarse para que el navegador reciba las nuevas
// URLs versionadas de CSS y JavaScript después de cada despliegue.
header('Cache-Control: no-cache, must-revalidate, max-age=0');

/**
 * Agrega una versión de caché a un recurso estático según su última modificación.
 */
function asset_version(string $path): string
{
    return is_file($path) ? '?v=' . filemtime($path) : '';
}

spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Configuración de sesión segura
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
