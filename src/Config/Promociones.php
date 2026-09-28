<?php

namespace Config;

/**
 * Banners públicos de promociones.
 *
 * Subí imágenes .jpg, .jpeg, .png o .webp a public/assets/img/promociones/.
 * El carrusel y la página de promociones las detectan y ordenan por nombre.
 */
class Promociones
{
    public static function obtenerActivas(): array
    {
        $raiz = dirname(__DIR__, 2);
        $directorios = [
            $raiz . '/public/assets/img/promociones',
            $raiz . '/assets/img/promociones',
        ];
        $archivos = [];

        foreach ($directorios as $directorio) {
            if (!is_dir($directorio)) {
                continue;
            }

            foreach (glob($directorio . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [] as $archivo) {
                $nombre = basename($archivo);
                $archivos[$nombre] ??= $archivo;
            }
        }

        uksort($archivos, 'strnatcasecmp');

        return array_map(
            static fn (string $nombre): array => [
                'id'     => 'promo-' . substr(sha1($nombre), 0, 12),
                'imagen' => 'assets/img/promociones/' . rawurlencode($nombre),
                'alt'    => 'Promoción de Imperio Comercial',
            ],
            array_keys($archivos)
        );
    }
}
