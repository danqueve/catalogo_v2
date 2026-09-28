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
        return 'https://wa.me/?text=' . rawurlencode($texto);
    }

}
