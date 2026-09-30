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

    /**
     * Construye la URL wa.me para consultar sobre un producto específico.
     */
    public static function urlProducto(string $nombre, string $plan = 'semanal', float $precioContado = 0, int $cuotasCant = 0, float $cuotasMonto = 0, string $productUrl = ''): string
    {
        $phone = defined('WA_PHONE') && WA_PHONE !== '' ? preg_replace('/[^0-9]/', '', WA_PHONE) : '';
        $baseUrl = $phone !== '' ? 'https://wa.me/' . $phone : 'https://wa.me/';

        $texto = "Hola! Quisiera consultar sobre el producto:\n* " . $nombre . "*";

        if ($plan === 'semanal' && $cuotasCant > 0 && $cuotasMonto > 0) {
            $texto .= "\n\nPlan seleccionado: *" . $cuotasCant . " cuotas semanales de $" . number_format($cuotasMonto, 0, ',', '.') . "*";
        } elseif ($plan === 'contado' && $precioContado > 0) {
            $texto .= "\n\nPlan seleccionado: *Contado $" . number_format($precioContado, 0, ',', '.') . "*";
        }

        if ($productUrl !== '') {
            $texto .= "\n\nEnlace: " . $productUrl;
        }

        return $baseUrl . '?text=' . rawurlencode($texto);
    }
}

