<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Models\Categoria;
use Models\Articulo;
use Helpers\Whatsapp;

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

$categoriaModel = new Categoria();
$categoria = $categoriaModel->obtenerPorSlug($slug);

if (!$categoria) {
    http_response_code(404);
    header('Location: index.php');
    exit;
}

$articuloModel = new Articulo();
$articulos     = $articuloModel->obtenerPorCategoria((int)$categoria['id']);

$ogTitle = htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8') . ' — Imperio Comercial';
$ogDesc  = 'Mirá todos los productos de ' . htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8') . ' con precios y financiación.';
$ogUrl   = BASE_URL . '/categoria.php?slug=' . urlencode($categoria['slug']);
$ogImage = !empty($categoria['imagen'])
           ? UPLOAD_URL . rawurlencode($categoria['imagen'])
           : BASE_URL . '/assets/img/logo.png';

function fmt(float $n): string {
    return number_format($n, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#ffffff">
  <title><?= $ogTitle ?></title>
  <meta name="description"         content="<?= $ogDesc ?>">
  <meta property="og:type"         content="website">
  <meta property="og:site_name"    content="Imperio Comercial">
  <meta property="og:title"        content="<?= $ogTitle ?>">
  <meta property="og:description"  content="<?= $ogDesc ?>">
  <meta property="og:url"          content="<?= $ogUrl ?>">
  <meta property="og:image"        content="<?= $ogImage ?>">
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="<?= $ogTitle ?>">
  <meta name="twitter:description" content="<?= $ogDesc ?>">
  <meta name="twitter:image"       content="<?= $ogImage ?>">
  <link rel="icon" type="image/png" href="assets/img/logo.png">
  <link rel="apple-touch-icon" href="assets/img/logo.png">
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">
  <link rel="stylesheet" href="assets/css/app.css">
  <?php include __DIR__ . '/partials/analytics.php'; ?>
</head>
<body>

<!-- Navbar -->
<nav class="navbar-glass px-3 py-2 d-flex align-items-center gap-2">
  <a href="index.php" class="back-btn">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
      <path d="M15 18l-6-6 6-6"/>
    </svg>
    Inicio
  </a>
  <span class="ms-auto fw-semibold text-truncate" style="font-size:.95rem;max-width:60vw;">
    <?= htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8') ?>
  </span>
</nav>

<main class="container-fluid px-3 py-4" style="max-width:960px;margin:0 auto;">

  <div class="badge-cat mb-1">
    <?= htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8') ?>
  </div>
  <h1 class="section-title">Productos</h1>
  <p class="section-subtitle">
    <?= count($articulos) ?> artículo<?= count($articulos) !== 1 ? 's' : '' ?> disponible<?= count($articulos) !== 1 ? 's' : '' ?>
  </p>

  <?php if (empty($articulos)): ?>
    <div class="empty-state">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="1.5">
        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
        <line x1="3" y1="6" x2="21" y2="6"/>
        <path d="M16 10a4 4 0 01-8 0"/>
      </svg>
      <p class="mt-2">Esta categoría aún no tiene productos.</p>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($articulos as $a):
        $waUrl = Whatsapp::urlArticulo($a, $categoria['slug']);
      ?>
        <div class="col-12 col-sm-6 col-lg-4" id="art-<?= (int)$a['id'] ?>">
          <article class="card-ios card-producto">

            <!-- Imagen 4:5 — clic abre lightbox -->
            <div class="ratio-4-5 zoomable"
                 data-lb-src="uploads/productos/<?= htmlspecialchars($a['imagen'], ENT_QUOTES, 'UTF-8') ?>"
                 data-lb-alt="<?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?>">
              <img src="uploads/productos/<?= htmlspecialchars($a['imagen'], ENT_QUOTES, 'UTF-8') ?>"
                   alt="<?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                   loading="lazy">
            </div>

            <div class="prod-body">
              <h2 class="prod-nombre"><?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?></h2>

              <?php if (!empty($a['descripcion'])): ?>
                <p style="font-size:.8rem;color:var(--text-2);margin:0;line-height:1.4;">
                  <?= nl2br(htmlspecialchars($a['descripcion'], ENT_QUOTES, 'UTF-8')) ?>
                </p>
              <?php endif; ?>

              <!-- Cuotas Semanales -->
              <?php if (!empty($a['cuotas_sem_cant']) && !empty($a['cuotas_sem_monto'])): ?>
                <div class="cuota-pill">
                  <span class="cuota-label">
                    <!-- Ícono calendario semanal -->
                    <svg class="cuota-icon" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8">
                      <rect x="3" y="4" width="18" height="18" rx="2"/>
                      <path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    Semanal
                  </span>
                  <strong><?= (int)$a['cuotas_sem_cant'] ?> × $<?= fmt((float)$a['cuotas_sem_monto']) ?></strong>
                </div>
              <?php endif; ?>

              <!-- Cuotas Mensuales -->
              <?php if (!empty($a['cuotas_mes_cant']) && !empty($a['cuotas_mes_monto'])): ?>
                <div class="cuota-pill">
                  <span class="cuota-label">
                    <svg class="cuota-icon" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8">
                      <rect x="3" y="4" width="18" height="18" rx="2"/>
                      <path d="M16 2v4M8 2v4M3 10h18"/>
                      <path d="M8 14h2l1 2 2-4 1 2h2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Mensual
                  </span>
                  <strong><?= (int)$a['cuotas_mes_cant'] ?> × $<?= fmt((float)$a['cuotas_mes_monto']) ?></strong>
                </div>
              <?php endif; ?>

              <!-- Precio contado (opcional) -->
              <?php if (!empty($a['precio_contado'])): ?>
                <div class="cuota-pill">
                  <span class="cuota-label">
                    <svg class="cuota-icon" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8">
                      <circle cx="12" cy="12" r="9"/>
                      <path d="M12 7v1m0 8v1M9.5 9.5a2.5 2.5 0 015 1c0 2-5 3-5 3a2.5 2.5 0 005 1"/>
                    </svg>
                    Contado
                  </span>
                  <strong class="precio-contado">$<?= fmt((float)$a['precio_contado']) ?></strong>
                </div>
              <?php endif; ?>

              <!-- Botón WhatsApp -->
              <a class="btn-wa"
                 href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>"
                 target="_blank" rel="noopener noreferrer"
                 aria-label="Compartir <?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?> por WhatsApp">
                <!-- Ícono WhatsApp oficial SVG -->
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Compartir por WhatsApp
              </a>

            </div><!-- /.prod-body -->
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</main>

<!-- Lightbox -->
<div class="lightbox-overlay" id="lightbox" role="dialog" aria-modal="true" aria-label="Imagen ampliada">
  <button class="lightbox-close" id="lightboxClose" aria-label="Cerrar">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
      <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
  </button>
  <img class="lightbox-img" id="lightboxImg" src="" alt="">
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
