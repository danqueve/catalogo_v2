<?php

namespace Config;

use Helpers\Upload;

/**
 * Banners públicos de promociones.
 *
 * Subí imágenes .jpg, .jpeg, .png o .webp a public/assets/img/promociones/.
 * El carrusel y la página de promociones las detectan y ordenan por nombre.
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
        foreach ([self::directorio(), self::directorioEspejo()] as $directorio) {
            if (!is_dir($directorio)) {
                continue;
            }

            foreach (glob($directorio . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [] as $archivo) {
                $nombre = basename($archivo);
                if (self::nombreSeguro($nombre)) {
                    $archivos[$nombre] ??= $archivo;
                }
            }
        }
        uksort($archivos, 'strnatcasecmp');
        return $archivos;
    }

    public static function obtenerActivas(): array
    {
        return array_map(
            static fn (string $nombre): array => [
                'id'     => 'promo-' . substr(sha1($nombre), 0, 12),
                'imagen' => 'assets/img/promociones/' . rawurlencode($nombre),
                'alt'    => 'Promoción de Imperio Comercial',
            ],
            array_keys(self::archivos())
        );
    }

    /** Lista de archivos para la administración, en el orden del carrusel. */
    public static function obtenerParaAdmin(): array
    {
        return array_map(
            static fn (string $nombre): array => [
                'nombre' => $nombre,
                'imagen' => '../public/assets/img/promociones/' . rawurlencode($nombre),
            ],
            array_keys(self::archivos())
        );
    }

    /** Guarda una imagen y la deja lista para el carrusel. */
    public static function subir(array $archivo, int $orden): string
    {
        $orden = max(1, min($orden, 999));
        $directorio = self::directorio();
        if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
            throw new \RuntimeException('No se pudo preparar la carpeta de promociones.');
        }

        $temporal = Upload::imagen($archivo, $directorio);
        $nombre = sprintf('%03d-', $orden) . $temporal;
        $origen = $directorio . '/' . $temporal;
        $destino = $directorio . '/' . $nombre;
        if (!rename($origen, $destino)) {
            @unlink($origen);
            throw new \RuntimeException('No se pudo ordenar la imagen cargada.');
        }

        $espejo = self::directorioEspejo();
        if (is_dir(dirname($espejo))) {
            if (!is_dir($espejo) && !mkdir($espejo, 0755, true) && !is_dir($espejo)) {
                @unlink($destino);
                throw new \RuntimeException('No se pudo preparar la carpeta pública de promociones.');
            }
            if (!copy($destino, $espejo . '/' . $nombre)) {
                @unlink($destino);
                throw new \RuntimeException('No se pudo publicar la imagen del carrusel.');
            }
        }

        return $nombre;
    }

    /** Elimina una promoción tanto del proyecto como del directorio público. */
    public static function eliminar(string $nombre): void
    {
        if (!self::nombreSeguro($nombre)) {
            throw new \RuntimeException('Archivo de promoción inválido.');
        }

        foreach ([self::directorio(), self::directorioEspejo()] as $directorio) {
            $ruta = $directorio . '/' . $nombre;
            if (is_file($ruta) && !@unlink($ruta)) {
                throw new \RuntimeException('No se pudo eliminar la imagen seleccionada.');
            }
        }
    }
}
