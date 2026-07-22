<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Models\Categoria;

$categoriaModel = new Categoria();
$categorias     = $categoriaModel->obtenerActivas();
$fijas          = array_filter($categorias, fn($c) => !empty($c['fijo']));
$resto          = array_filter($categorias, fn($c) => empty($c['fijo']));

$hero = !empty($fijas) ? array_values($fijas)[0] : null;
$grid = !empty($fijas) ? array_values($resto) : array_values($categorias);

$ogImageCat = null;
foreach (array_merge(array_values($fijas), array_values($resto)) as $c) {
    if (!empty($c['imagen'])) { $ogImageCat = $c['imagen']; break; }
}
$ogImage = $ogImageCat ? UPLOAD_URL . rawurlencode($ogImageCat) : null;
$waFab   = WA_PHONE ? 'https://wa.me/' . WA_PHONE : 'https://wa.me/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#f5ead8">
  <title>Imperio Comercial Tucumán | Muebles, Electrodomésticos y Asadores en Cuotas | Solo tu DNI</title>
  <meta name="description"         content="Comprá muebles, electrodomésticos y asadores en cuotas semanales en Tucumán. Solo necesitás tu DNI. Entrega gratis dentro de Tucumán. Visitanos en Corrientes 2200.">
  <meta property="og:type"         content="website">
  <meta property="og:site_name"    content="Imperio Comercial Tucumán">
  <meta property="og:title"        content="Imperio Comercial Tucumán | Muebles y Electrodomésticos en Cuotas | Solo tu DNI">
  <meta property="og:description"  content="Comprá muebles, electrodomésticos y asadores en cuotas semanales en Tucumán. Solo necesitás tu DNI. Entrega gratis dentro de Tucumán.">
  <meta property="og:url"          content="<?= BASE_URL ?>">
  <?php if ($ogImage): ?>
  <meta property="og:image"        content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="Imperio Comercial Tucumán | Catálogo">
  <meta name="twitter:description" content="Comprá en cuotas semanales con solo tu DNI. Tucumán, Sgo. del Estero y Catamarca.">
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
    <?php if (!empty($categorias)): ?>
    <button id="icMenuButton" class="ic-menu-btn" type="button"
            aria-label="Mostrar categorías" aria-controls="icCategoryMenu" aria-expanded="false">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.75" stroke-linecap="round">
        <path d="M3 12h18M3 6h18M3 18h18"/>
      </svg>
    </button>
    <?php endif; ?>
  </div>
  <div class="ic-search-wrap">
    <svg class="ic-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
    </svg>
    <input type="search" class="ic-search"
           placeholder='Buscar productos… "smart tv", "living"'
           aria-label="Buscar productos" autocomplete="off">
  </div>
  <?php if (!empty($categorias)): ?>
  <nav id="icCategoryMenu" class="ic-category-menu" aria-label="Categorías" hidden>
    <p class="ic-category-menu-title">Categorías</p>
    <?php foreach ($categorias as $cat): ?>
    <a href="categoria.php?slug=<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>">
      <?= htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8') ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <?php endif; ?>
</header>

<!-- FRANJA CRÉDITO -->
<div class="ic-credit-bar">
  <strong>Crédito personal en el acto</strong>
  <span>· Entregas en Tucumán, Sgo. del Estero y Catamarca</span>
</div>

<!-- CHIPS DE CATEGORÍAS -->
<?php if (!empty($categorias)): ?>
<div class="ic-chips-outer">
  <div class="ic-chips">
    <?php if ($hero): ?>
    <button class="ic-chip ic-chip-active">Promos del mes</button>
    <?php endif; ?>
    <?php foreach ($categorias as $cat): ?>
    <button class="ic-chip"><?= htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8') ?></button>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

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
      <p class="ic-hero-desc">Hasta 10 cuotas semanales fijas, sin interés.</p>
      <a href="categoria.php?slug=<?= htmlspecialchars($hero['slug'], ENT_QUOTES, 'UTF-8') ?>"
         class="ic-btn-primary">Ver promos →</a>
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

<!-- BLOQUE ¿CÓMO FUNCIONA EL CRÉDITO? -->
<div class="ic-credit-block-outer">
  <div class="ic-credit-block">
    <h3 class="ic-credit-block-title">¿Cómo funciona el crédito?</h3>
    <p class="ic-credit-block-desc">Con tu DNI, en el acto. Elegís el producto, te decimos la cuota semanal y coordinamos la entrega.</p>
    <a href="<?= htmlspecialchars($waFab, ENT_QUOTES, 'UTF-8') ?>"
       target="_blank" rel="noopener noreferrer"
       class="ic-btn-outline">Consultar requisitos</a>
  </div>
</div>

<script src="assets/js/ic.js" defer></script>
</body>
</html>
