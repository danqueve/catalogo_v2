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

<script src="assets/js/ic.js" defer></script>
</body>
</html>
