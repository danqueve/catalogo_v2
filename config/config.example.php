<?php
define('DB_HOST',     'localhost');
define('DB_NAME',     'catalogo');
define('DB_USER',     'root');
define('DB_PASS',     '');
define('DB_CHARSET',  'utf8mb4');

// URL base del proyecto (sin barra final)
define('BASE_URL', 'http://localhost/cat2/public');

// Ruta absoluta a la carpeta de uploads de productos
define('UPLOAD_DIR', dirname(__DIR__) . '/public/uploads/productos/');
define('UPLOAD_URL', BASE_URL . '/uploads/productos/');

// Número WhatsApp del negocio (con código de país, sin +). Vacío = el vendedor elige el contacto.
define('WA_PHONE', '');

// Límite de tamaño de imagen (bytes) — 3 MB
define('UPLOAD_MAX_SIZE', 3 * 1024 * 1024);
