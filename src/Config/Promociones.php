<?php

namespace Config;

/**
 * Fuente única de promociones públicas.
 *
 * Para publicar una nueva promoción, agregá un bloque al arreglo con `activo`
 * en true. `tono` admite: naranja, violeta o azul.
 */
class Promociones
{
    private const ITEMS = [
        [
            'id'          => 'cuotas-fijas',
            'activo'      => true,
            'orden'       => 1,
            'etiqueta'    => 'Promoción destacada',
            'titulo'      => 'Hasta 12 cuotas fijas',
            'descripcion' => 'Elegí lo que necesitás y consultá las opciones de financiación disponibles.',
            'cta'         => 'Consultar financiación',
            'enlace'      => 'consulta.php',
            'tono'        => 'naranja',
        ],
        [
            'id'          => 'credito-inmediato',
            'activo'      => true,
            'orden'       => 2,
            'etiqueta'    => 'Crédito personal',
            'titulo'      => 'Con tu DNI, en el acto',
            'descripcion' => 'Te asesoramos para que encuentres una cuota que se adapte a vos.',
            'cta'         => 'Ver cómo funciona',
            'enlace'      => 'consulta.php',
            'tono'        => 'violeta',
        ],
        [
            'id'          => 'entrega-tucuman',
            'activo'      => true,
            'orden'       => 3,
            'etiqueta'    => 'Beneficio de compra',
            'titulo'      => 'Entrega gratis en Tucumán',
            'descripcion' => 'Consultá por la cobertura de entrega de tu producto y zona.',
            'cta'         => 'Explorar el catálogo',
            'enlace'      => 'index.php#categorias',
            'tono'        => 'azul',
        ],
    ];

    public static function obtenerActivas(): array
    {
        $promociones = array_values(array_filter(
            self::ITEMS,
            static fn (array $promocion): bool => !empty($promocion['activo'])
        ));

        usort(
            $promociones,
            static fn (array $a, array $b): int => ($a['orden'] ?? 0) <=> ($b['orden'] ?? 0)
        );

        return $promociones;
    }
}
