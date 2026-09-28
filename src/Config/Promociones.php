<?php

namespace Config;

use Helpers\Upload;

/**
 * Banners públicos de promociones.
 *
 * Las imágenes administradas se guardan en uploads/promociones/, fuera del
 * repositorio, para que puedan cargarse desde el panel sin permisos de Git.
 */
class Promociones
{
    private const EXTENSIONES = ['jpg', 'jpeg', 'png', 'webp'];

    private static function raiz(): string
    {
        return dirname(__DIR__, 2);
    }

    /** Directorio fuente, disponible en el proyecto y durante el despliegue. */
    public static function directorio(): string
    {
        return self::raiz() . '/public/assets/img/promociones';
    }

    /** Directorio escribible por PHP para las imágenes cargadas desde admin. */
    private static function directorioCarga(): string
    {
        return dirname(rtrim(UPLOAD_DIR, '/\\')) . '/promociones';
    }

    private static function urlCarga(): string
    {
        $url = rtrim(UPLOAD_URL, '/');
        $posicion = strrpos($url, '/');
        $base = $posicion === false ? $url : substr($url, 0, $posicion);
        return rtrim($base, '/') . '/promociones/';
    }

    /** Directorio visible directamente en el VPS después de sincronizar public/. */
    private static function directorioEspejo(): string
    {
        return self::raiz() . '/assets/img/promociones';
    }

    private static function nombreSeguro(string $nombre): bool
    {
        $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        return $nombre === basename($nombre)
            && in_array($extension, self::EXTENSIONES, true)
            && preg_match('/^[a-z0-9][a-z0-9._-]*$/i', $nombre) === 1;
    }

    private static function archivos(): array
    {
        $archivos = [];
        $ubicaciones = [
            ['directorio' => self::directorioCarga(), 'url' => self::urlCarga()],
            ['directorio' => self::directorio(), 'url' => 'assets/img/promociones/'],
            ['directorio' => self::directorioEspejo(), 'url' => 'assets/img/promociones/'],
        ];

        foreach ($ubicaciones as $ubicacion) {
            $directorio = $ubicacion['directorio'];
            if (!is_dir($directorio)) {
                continue;
            }

            foreach (glob($directorio . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [] as $archivo) {
                $nombre = basename($archivo);
                if (self::nombreSeguro($nombre)) {
                    $archivos[$nombre] ??= [
                        'ruta' => $archivo,
                        'url' => $ubicacion['url'] . rawurlencode($nombre),
                    ];
                }
            }
        }
        uksort($archivos, 'strnatcasecmp');
        return $archivos;
    }

    public static function obtenerActivas(): array
    {
        $promociones = [];
        foreach (self::archivos() as $nombre => $archivo) {
            $promociones[] = [
                'id'     => 'promo-' . substr(sha1($nombre), 0, 12),
                'imagen' => $archivo['url'],
                'alt'    => 'Promoción de Imperio Comercial',
            ];
        }
        return $promociones;
    }

    /** Lista de archivos para la administración, en el orden del carrusel. */
    public static function obtenerParaAdmin(): array
    {
        $promociones = [];
        foreach (self::archivos() as $nombre => $archivo) {
            $promociones[] = [
                'nombre' => $nombre,
                'imagen' => $archivo['url'],
            ];
        }
        return $promociones;
    }

    /** Guarda una imagen y la deja lista para el carrusel. */
    public static function subir(array $archivo, int $orden): string
    {
        $orden = max(1, min($orden, 999));
        $directorio = self::directorioCarga();
        if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
            throw new \RuntimeException('No se pudo preparar la carpeta de promociones.');
        }
        if (!is_writable($directorio)) {
            throw new \RuntimeException('La carpeta de promociones no tiene permisos de escritura en el servidor.');
        }

        $temporal = Upload::imagen($archivo, $directorio);
        $nombre = sprintf('%03d-', $orden) . $temporal;
        $origen = $directorio . '/' . $temporal;
        $destino = $directorio . '/' . $nombre;
        if (!rename($origen, $destino)) {
            @unlink($origen);
            throw new \RuntimeException('No se pudo ordenar la imagen cargada.');
        }

        return $nombre;
    }

    /** Elimina una promoción tanto del proyecto como del directorio público. */
    public static function eliminar(string $nombre): void
    {
        if (!self::nombreSeguro($nombre)) {
            throw new \RuntimeException('Archivo de promoción inválido.');
        }

        foreach ([self::directorioCarga(), self::directorio(), self::directorioEspejo()] as $directorio) {
            $ruta = $directorio . '/' . $nombre;
            if (is_file($ruta) && !@unlink($ruta)) {
                throw new \RuntimeException('No se pudo eliminar la imagen seleccionada.');
            }
        }
    }
}
