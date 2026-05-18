<?php

namespace Helpers;

class Upload
{
    private const MIME_PERMITIDOS = ['image/jpeg', 'image/png', 'image/webp'];
    private const EXT_PERMITIDAS  = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Procesa la subida de una imagen. Devuelve el nombre del archivo guardado.
     * Lanza \RuntimeException en caso de error.
     */
    public static function imagen(array $archivo, string $directorio): string
    {
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Error al subir el archivo (código: ' . $archivo['error'] . ').');
        }

        if ($archivo['size'] > UPLOAD_MAX_SIZE) {
            throw new \RuntimeException('La imagen supera el tamaño máximo de 3 MB.');
        }

        // Verificar MIME real
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($archivo['tmp_name']);
        if (!in_array($mime, self::MIME_PERMITIDOS, true)) {
            throw new \RuntimeException('Tipo de archivo no permitido. Use JPG, PNG o WebP.');
        }

        $ext      = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::EXT_PERMITIDAS, true)) {
            throw new \RuntimeException('Extensión no permitida.');
        }

        // Nombre único para evitar colisiones
        $nombre = bin2hex(random_bytes(16)) . '.' . $ext;
        $destino = rtrim($directorio, '/') . '/' . $nombre;

        if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
            throw new \RuntimeException('No se pudo guardar la imagen en el servidor.');
        }

        return $nombre;
    }

    public static function borrar(string $directorio, string $nombre): void
    {
        if (empty($nombre)) return;
        $ruta = rtrim($directorio, '/') . '/' . $nombre;
        if (file_exists($ruta)) {
            @unlink($ruta);
        }
    }
}
