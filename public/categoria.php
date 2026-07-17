<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Models\Categoria;
use Models\Articulo;

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

$n       = count($articulos);
$nLabel  = $n . ' artículo' . ($n !== 1 ? 's' : '');

$ogTitle = htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8') . ' — Imperio Comercial';
$ogDesc  = 'Mirá todos los productos de ' . htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8') . ' con precios y financiación.';
$ogUrl   = BASE_URL . '/categoria.php?slug=' . urlencode($categoria['slug']);
$ogImage = !empty($categoria['imagen'])
           ? UPLOAD_URL . rawurlencode($categoria['imagen'])
           : BASE_URL . '/assets/img/logo.png';

$waFab = WA_PHONE ? 'https://wa.me/' . WA_PHONE : 'https://wa.me/';

function fmt(float $n): string {
    return number_format($n, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#f5ead8">
  <title><?= $ogTitle ?></title>
  <meta name="description"        content="<?= $ogDesc ?>">
  <meta property="og:type"        content="website">
  <meta property="og:site_name"   content="Imperio Comercial">
  <meta property="og:title"       content="<?= $ogTitle ?>">
  <meta property="og:description" content="<?= $ogDesc ?>">
  <meta property="og:url"         content="<?= $ogUrl ?>">
  <meta property="og:image"       content="<?= $ogImage ?>">
  <meta name="twitter:card"       content="summary_large_image">
  <meta name="twitter:title"      content="<?= $ogTitle ?>">
  <meta name="twitter:image"      content="<?= $ogImage ?>">
  <link rel="icon" type="image/png" href="assets/img/logo.png">
  <link rel="apple-touch-icon"      href="assets/img/logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caprasimo&family=Figtree:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/app.css">
  <?php include __DIR__ . '/partials/analytics.php'; ?>
</head>
<body class="ic-page">

<!-- HEADER -->
<header class="ic-header-back">
  <button class="ic-back-btn" onclick="history.back()" aria-label="Volver">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M15 18l-6-6 6-6"/>
    </svg>
  </button>
  <div class="ic-header-back-info">
    <h1 class="ic-page-title"><?= htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="ic-page-subtitle"><?= $nLabel ?></p>
  </div>
  <a href="consulta.php" class="ic-cart-link" style="font-size:13px;color:var(--ic-a2-600);font-weight:600;white-space:nowrap;flex-shrink:0;text-decoration:none;" aria-label="Mi consulta">
    Mi consulta<span data-cart-count style="margin-left:3px;"></span>
  </a>
</header>

<!-- CHIPS FILTRO -->
<div class="ic-chips-outer">
  <div class="ic-chips">
    <button class="ic-chip ic-chip-filter-active" data-filter="all">Todos</button>
    <button class="ic-chip" data-filter="credito">En crédito</button>
    <button class="ic-chip" data-filter="menor">Menor precio</button>
  </div>
</div>

<!-- GRILLA PRODUCTOS -->
<?php if (empty($articulos)): ?>
<div class="ic-empty">Esta categoría aún no tiene productos.</div>
<?php else: ?>
<div class="ic-prod-grid" id="icProdGrid">
  <?php foreach ($articulos as $a):
    $tieneCredito = (!empty($a['cuotas_sem_cant']) && !empty($a['cuotas_sem_monto'])) ? '1' : '0';
    $contado      = !empty($a['precio_contado']) ? (float)$a['precio_contado'] : 0;
  ?>
  <a href="producto.php?id=<?= (int)$a['id'] ?>" class="ic-prod-card"
     data-tiene-credito="<?= $tieneCredito ?>"
     data-contado="<?= $contado ?>">

    <div class="ic-prod-img">
      <?php if (!empty($a['imagen'])): ?>
        <img src="<?= UPLOAD_URL . htmlspecialchars($a['imagen'], ENT_QUOTES, 'UTF-8') ?>"
             alt="<?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?>"
             loading="lazy">
      <?php else: ?>
        <div class="ic-prod-img-ph">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="1.5" opacity=".35">
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <circle cx="8.5" cy="8.5" r="1.5"/>
            <path d="M21 15l-5-5L5 21"/>
          </svg>
        </div>
      <?php endif; ?>
    </div>

    <div class="ic-prod-body">
      <p class="ic-prod-nombre"><?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?></p>
      <?php if ($tieneCredito === '1'): ?>
        <p class="ic-prod-cuota">
          <?= (int)$a['cuotas_sem_cant'] ?> × $<?= fmt((float)$a['cuotas_sem_monto']) ?> <span>/sem</span>
        </p>
      <?php endif; ?>
      <?php if ($contado > 0): ?>
        <p class="ic-prod-contado">Contado $<?= fmt($contado) ?></p>
      <?php endif; ?>
    </div>

  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- FAB WHATSAPP -->
<a href="<?= htmlspecialchars($waFab, ENT_QUOTES, 'UTF-8') ?>"
   class="ic-fab-wa" target="_blank" rel="noopener noreferrer"
   aria-label="Consultar por WhatsApp">
  <svg width="26" height="26" viewBox="0 0 32 32" fill="currentColor">
    <path d="M16 2C8.27 2 2 8.27 2 16c0 2.44.66 4.82 1.9 6.9L2 30l7.34-1.87A13.94 13.94 0 0 0 16 30c7.73 0 14-6.27 14-14S23.73 2 16 2zm7.6 19.4c-.32.9-1.87 1.72-2.58 1.82-.66.1-1.5.14-2.42-.15-.56-.18-1.28-.42-2.2-.82-3.88-1.68-6.42-5.6-6.62-5.86-.2-.26-1.6-2.13-1.6-4.06 0-1.93 1.01-2.88 1.37-3.27.36-.39.78-.49 1.04-.49.26 0 .52 0 .75.01.24.01.56-.09.88.67.32.78 1.1 2.7 1.2 2.9.1.2.16.43.03.69-.13.26-.2.42-.39.65-.2.23-.41.51-.59.69-.19.18-.39.38-.17.74.22.36.99 1.63 2.13 2.64 1.46 1.3 2.69 1.7 3.05 1.89.36.19.57.16.78-.1.21-.26.9-1.05 1.14-1.41.24-.36.48-.3.81-.18.33.12 2.1.99 2.46 1.17.36.18.6.27.69.42.09.16.09.9-.23 1.8z"/>
  </svg>
</a>

<script src="assets/js/ic.js" defer></script>
</body>
</html>
