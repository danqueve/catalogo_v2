<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Models\Categoria;
use Models\Articulo;
use Config\Promociones;

$categoriaModel = new Categoria();
$categorias     = $categoriaModel->obtenerActivas();
$fijas          = array_filter($categorias, fn($c) => !empty($c['fijo']));
$resto          = array_filter($categorias, fn($c) => empty($c['fijo']));

$hero = !empty($fijas) ? array_values($fijas)[0] : null;
$grid = !empty($fijas) ? array_values($resto) : array_values($categorias);
$promociones = Promociones::obtenerActivas();
$busqueda = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$busqueda = mb_substr($busqueda, 0, 80);
$resultadosBusqueda = $busqueda !== '' ? (new Articulo())->buscarActivos($busqueda) : [];

$ogImageCat = null;
foreach (array_merge(array_values($fijas), array_values($resto)) as $c) {
    if (!empty($c['imagen'])) { $ogImageCat = $c['imagen']; break; }
}
$ogImage = $ogImageCat ? UPLOAD_URL . rawurlencode($ogImageCat) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#155e63">
  <title>Imperio Comercial Tucumán | Muebles, Electrodomésticos y Asadores</title>
  <meta name="description"         content="Muebles, electrodomésticos y asadores para tu hogar en Tucumán. Conocé el catálogo y las promociones vigentes.">
  <meta property="og:type"         content="website">
  <meta property="og:site_name"    content="Imperio Comercial Tucumán">
  <meta property="og:title"        content="Imperio Comercial Tucumán | Muebles y Electrodomésticos">
  <meta property="og:description"  content="Conocé muebles, electrodomésticos, asadores y las promociones vigentes de Imperio Comercial Tucumán.">
  <meta property="og:url"          content="<?= BASE_URL ?>">
  <?php if ($ogImage): ?>
  <meta property="og:image"        content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="Imperio Comercial Tucumán | Catálogo">
  <meta name="twitter:description" content="Muebles, electrodomésticos y promociones de Imperio Comercial Tucumán.">
  <meta name="twitter:image"       content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>
  <link rel="icon" type="image/png" href="assets/img/logo.png">
  <link rel="apple-touch-icon"      href="assets/img/logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caprasimo&family=Figtree:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/app.css">
  <?php include __DIR__ . '/partials/analytics.php'; ?>
</head>
<body class="ic-page">

<!-- HEADER STICKY -->
<header class="ic-header">
  <div class="ic-header-inner">
    <a href="index.php" class="ic-brand">Imperio <span>Comercial</span></a>
    <a href="promociones.php" class="ic-header-link">Promociones</a>
  </div>
  <form class="ic-search-wrap" action="index.php" method="get" role="search">
    <svg class="ic-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
    </svg>
    <input type="search" class="ic-search" name="q" value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>"
           placeholder='Buscar productos… "smart tv", "living"'
           aria-label="Buscar productos" autocomplete="off">
    <button class="ic-search-submit" type="submit">Buscar</button>
  </form>
</header>

<!-- PROMOCIONES -->
<?php if (!empty($promociones)): ?>
<section class="ic-promo-carousel" data-promo-carousel aria-roledescription="carrusel" aria-label="Promociones destacadas">
  <div class="ic-promo-carousel-track">
    <?php foreach ($promociones as $indice => $promocion): ?>
    <article class="ic-promo-slide<?= $indice === 0 ? ' is-active' : '' ?>"
             data-promo-slide aria-hidden="<?= $indice === 0 ? 'false' : 'true' ?>">
      <a href="promociones.php" class="ic-promo-image-link" aria-label="Ver promociones">
        <img src="<?= htmlspecialchars($promocion['imagen'], ENT_QUOTES, 'UTF-8') ?>"
             alt="<?= htmlspecialchars($promocion['alt'], ENT_QUOTES, 'UTF-8') ?>"
             <?= $indice === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
      </a>
    </article>
    <?php endforeach; ?>
  </div>

  <?php if (count($promociones) > 1): ?>
  <div class="ic-promo-controls">
    <div class="ic-promo-indicators" role="tablist" aria-label="Elegir promoción">
      <?php foreach ($promociones as $indice => $promocion): ?>
      <button type="button" data-promo-indicator="<?= $indice ?>" role="tab"
              aria-label="Mostrar promoción <?= $indice + 1 ?>"
              aria-selected="<?= $indice === 0 ? 'true' : 'false' ?>"></button>
      <?php endforeach; ?>
    </div>
    <div class="ic-promo-arrows">
      <button type="button" class="ic-promo-arrow" data-promo-prev aria-label="Promoción anterior">‹</button>
      <button type="button" class="ic-promo-arrow" data-promo-next aria-label="Promoción siguiente">›</button>
    </div>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($busqueda !== ''): ?>
<main class="ic-search-results">
  <header class="ic-section-head">
    <div>
      <p class="ic-search-kicker">Resultados de búsqueda</p>
      <h1 class="ic-section-title">“<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>”</h1>
    </div>
    <a href="index.php" class="ic-clear-search">Limpiar</a>
  </header>
  <?php if (!empty($resultadosBusqueda)): ?>
  <div class="ic-prod-grid ic-search-grid">
    <?php foreach ($resultadosBusqueda as $a): ?>
    <article class="ic-prod-card">
      <?php if (!empty($a['imagen'])): ?>
      <button class="ic-prod-img ic-image-trigger" type="button"
              data-image-src="<?= UPLOAD_URL . htmlspecialchars($a['imagen'], ENT_QUOTES, 'UTF-8') ?>"
              data-image-alt="<?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?>"
              aria-label="Ampliar imagen de <?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?>">
        <img src="<?= UPLOAD_URL . htmlspecialchars($a['imagen'], ENT_QUOTES, 'UTF-8') ?>"
             alt="<?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
      </button>
      <?php else: ?>
      <div class="ic-prod-img ic-prod-img-ph" aria-hidden="true"></div>
      <?php endif; ?>
      <div class="ic-prod-body">
        <p class="ic-prod-category"><?= htmlspecialchars($a['categoria_nombre'], ENT_QUOTES, 'UTF-8') ?></p>
        <p class="ic-prod-nombre"><?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?></p>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="ic-empty">No encontramos productos para esa búsqueda.</div>
  <?php endif; ?>
</main>
<?php else: ?>
<!-- HERO (primera categoría fija) -->
<?php if ($hero): ?>
<div class="ic-hero-outer">
  <div class="ic-hero-card">
    <div class="ic-hero-img">
      <?php if (!empty($hero['imagen'])): ?>
        <img src="<?= UPLOAD_URL . htmlspecialchars($hero['imagen'], ENT_QUOTES, 'UTF-8') ?>"
             alt="<?= htmlspecialchars($hero['nombre'], ENT_QUOTES, 'UTF-8') ?>"
             loading="eager">
      <?php else: ?>
        <div class="ic-hero-img-ph"></div>
      <?php endif; ?>
    </div>
    <div class="ic-hero-body">
      <h2 class="ic-hero-title"><?= htmlspecialchars($hero['nombre'], ENT_QUOTES, 'UTF-8') ?></h2>
      <p class="ic-hero-desc">Encontrá productos seleccionados para renovar cada espacio.</p>
      <a href="categoria.php?slug=<?= htmlspecialchars($hero['slug'], ENT_QUOTES, 'UTF-8') ?>"
         class="ic-btn-primary">Explorar categoría</a>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- GRILLA CATEGORÍAS -->
<?php if (!empty($grid)): ?>
<section class="ic-section">
  <div class="ic-section-head">
    <h2 class="ic-section-title">Categorías</h2>
  </div>
  <div class="ic-cat-grid">
    <?php foreach ($grid as $cat): ?>
    <a href="categoria.php?slug=<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>"
       class="ic-cat-card">
      <div class="ic-cat-img">
        <?php if (!empty($cat['imagen'])): ?>
          <img src="<?= UPLOAD_URL . htmlspecialchars($cat['imagen'], ENT_QUOTES, 'UTF-8') ?>"
               alt="<?= htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8') ?>"
               loading="lazy">
        <?php else: ?>
          <div class="ic-cat-img-ph">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".4">
              <rect x="3" y="3" width="18" height="18" rx="2"/>
              <circle cx="8.5" cy="8.5" r="1.5"/>
              <path d="M21 15l-5-5L5 21"/>
            </svg>
          </div>
        <?php endif; ?>
      </div>
      <span class="ic-cat-name"><?= htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php elseif (empty($fijas)): ?>
<div class="ic-empty">Aún no hay categorías disponibles.</div>
<?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/partials/image-viewer.php'; ?>
<script src="assets/js/ic.js" defer></script>
</body>
</html>
