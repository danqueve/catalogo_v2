# Carrusel de promociones por imagen

## Goal

Dejar sólo banners de imagen que enlacen a promociones y retirar los accesos de contacto por WhatsApp.

## Tasks

- [x] Detectar automáticamente imágenes de `public/assets/img/promociones/` → Verificar: sólo archivos de imagen generan slides.
- [x] Reemplazar el contenido textual y CTA del carrusel por enlaces de imagen → Verificar: cada slide lleva a `promociones.php`.
- [x] Retirar FAB y CTA de financiación; añadir sólo compartir categoría → Verificar: no quedan enlaces de contacto WhatsApp.
- [ ] Validar PHP, JavaScript y diff → Verificar: no hay errores de sintaxis ni marcadores pendientes.

## Done When

- [ ] El carrusel muestra exclusivamente banners de imagen y las promociones se cargan subiendo archivos al directorio indicado.
- [ ] Sólo la acción de compartir una categoría usa WhatsApp.
