<?php

namespace Helpers;

class Whatsapp
{
    /**
     * Construye la URL wa.me para compartir una categoría completa.
     */
    public static function urlCategoria(string $nombre, string $slug): string
    {
        $url   = BASE_URL . '/categoria.php?slug=' . urlencode($slug);
        $texto = '🛍️ *' . $nombre . "*\n\nMirá todos los productos de esta categoría:\n🔗 " . $url;
        $base  = WA_PHONE ? 'https://wa.me/' . WA_PHONE : 'https://wa.me/';
        return $base . '?text=' . rawurlencode($texto);
    }

    /**
     * Construye la URL wa.me con el texto del artículo pre-formateado.
     */
    public static function urlArticulo(array $articulo, string $categoriaSlug): string
    {
        $lineas = [];
        $lineas[] = '*' . $articulo['nombre'] . '*';

        if (!empty($articulo['descripcion'])) {
            $lineas[] = $articulo['descripcion'];
        }

        $lineas[] = '';
        $lineas[] = '💳 *Opciones de pago:*';

        if (!empty($articulo['cuotas_sem_cant']) && !empty($articulo['cuotas_sem_monto'])) {
            $monto = number_format((float)$articulo['cuotas_sem_monto'], 0, ',', '.');
            $lineas[] = '• Semanal: ' . (int)$articulo['cuotas_sem_cant'] . ' × $' . $monto;
        }

        if (!empty($articulo['cuotas_mes_cant']) && !empty($articulo['cuotas_mes_monto'])) {
            $monto = number_format((float)$articulo['cuotas_mes_monto'], 0, ',', '.');
            $lineas[] = '• Mensual: ' . (int)$articulo['cuotas_mes_cant'] . ' × $' . $monto;
        }

        if (!empty($articulo['precio_contado'])) {
            $precio = number_format((float)$articulo['precio_contado'], 0, ',', '.');
            $lineas[] = '• Contado: $' . $precio;
        }

        $lineas[] = '';
        $url = BASE_URL . '/categoria.php?slug=' . urlencode($categoriaSlug) . '#art-' . $articulo['id'];
        $lineas[] = '🔗 Ver producto: ' . $url;

        $texto = implode("\n", $lineas);

        $base = WA_PHONE ? 'https://wa.me/' . WA_PHONE : 'https://wa.me/';
        return $base . '?text=' . rawurlencode($texto);
    }
}
