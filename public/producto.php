<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Models\Articulo;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$articuloModel = new Articulo();
$a = $articuloModel->obtenerPorId($id);

if (!$a || empty($a['activo'])) {
    http_response_code(404);
    header('Location: index.php');
    exit;
}

function fmt(float $n): string {
    return number_format($n, 0, ',', '.');
}

$tieneSemanal = !empty($a['cuotas_sem_cant']) && !empty($a['cuotas_sem_monto']);
$tieneContado = !empty($a['precio_contado']);

$waBase = WA_PHONE ? 'https://wa.me/' . WA_PHONE : 'https://wa.me/';
$pageUrl = BASE_URL . '/producto.php?id=' . $id;

$waUrlSemanal = '';
if ($tieneSemanal) {
    $msg = '*' . $a['nombre'] . "*\n\n";
    $msg .= '💳 Cuota semanal: ' . (int)$a['cuotas_sem_cant'] . ' × $' . fmt((float)$a['cuotas_sem_monto']) . "\n";
    $msg .= '🔗 ' . $pageUrl;
    $waUrlSemanal = $waBase . '?text=' . rawurlencode($msg);
}

$waUrlContado = '';
if ($tieneContado) {
    $msg = '*' . $a['nombre'] . "*\n\n";
    $msg .= '💳 Contado: $' . fmt((float)$a['precio_contado']) . "\n";
    $msg .= '🔗 ' . $pageUrl;
    $waUrlContado = $waBase . '?text=' . rawurlencode($msg);
}

$waUrlDefault  = $waUrlSemanal ?: $waUrlContado;
$defaultPlan   = $tieneSemanal ? 'semanal' : 'contado';

$totalSemanal = $tieneSemanal
    ? (int)$a['cuotas_sem_cant'] * (float)$a['cuotas_sem_monto']
    : 0;

$catSlug   = $a['categoria_slug'] ?? '';
$catNombre = $a['categoria_nombre'] ?? 'Categoría';

$ogTitle = htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') . ' — Imperio Comercial';
$ogDesc  = !empty($a['descripcion'])
    ? htmlspecialchars(mb_strimwidth($a['descripcion'], 0, 140, '…'), ENT_QUOTES, 'UTF-8')
    : 'Ver precio y financiación en Imperio Comercial.';
$ogImage = !empty($a['imagen']) ? UPLOAD_URL . rawurlencode($a['imagen']) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#f5ead8">
  <title><?= $ogTitle ?></title>
  <meta name="description"        content="<?= $ogDesc ?>">
  <meta property="og:type"        content="product">
  <meta property="og:site_name"   content="Imperio Comercial">
  <meta property="og:title"       content="<?= $ogTitle ?>">
  <meta property="og:description" content="<?= $ogDesc ?>">
  <meta property="og:url"         content="<?= htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8') ?>">
  <?php if ($ogImage): ?>
  <meta property="og:image"       content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:card"       content="summary_large_image">
  <meta name="twitter:image"      content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
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

<!-- HEADER -->
<header class="ic-header-back">
  <button class="ic-back-btn" onclick="history.back()" aria-label="Volver">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M15 18l-6-6 6-6"/>
    </svg>
  </button>
  <a href="categoria.php?slug=<?= htmlspecialchars($catSlug, ENT_QUOTES, 'UTF-8') ?>"
     class="ic-breadcrumb">
    <?= htmlspecialchars($catNombre, ENT_QUOTES, 'UTF-8') ?>
  </a>
  <a href="consulta.php" style="margin-left:auto;font-size:13px;color:var(--ic-a2-600);font-weight:600;white-space:nowrap;flex-shrink:0;text-decoration:none;" aria-label="Mi consulta">
    Mi consulta<span data-cart-count style="margin-left:3px;"></span>
  </a>
</header>

<!-- IMAGEN -->
<div class="ic-ficha-img">
  <?php if (!empty($a['imagen'])): ?>
    <img src="<?= UPLOAD_URL . htmlspecialchars($a['imagen'], ENT_QUOTES, 'UTF-8') ?>"
         alt="<?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?>"
         loading="eager">
  <?php else: ?>
    <div class="ic-ficha-img-ph">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="1.2" opacity=".3">
        <rect x="3" y="3" width="18" height="18" rx="2"/>
        <circle cx="8.5" cy="8.5" r="1.5"/>
        <path d="M21 15l-5-5L5 21"/>
      </svg>
    </div>
  <?php endif; ?>
</div>

<!-- CUERPO -->
<div class="ic-ficha-body">

  <?php if ($tieneSemanal): ?>
  <span class="ic-tag-credito">Disponible en crédito</span>
  <?php endif; ?>

  <h1 class="ic-ficha-titulo"><?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?></h1>

  <?php if (!empty($a['descripcion'])): ?>
  <p class="ic-ficha-desc"><?= nl2br(htmlspecialchars($a['descripcion'], ENT_QUOTES, 'UTF-8')) ?></p>
  <?php endif; ?>

  <!-- SELECTOR DE PLAN -->
  <?php if ($tieneSemanal || $tieneContado): ?>
  <div class="ic-plan-card">

    <?php if ($tieneSemanal): ?>
    <button class="ic-plan-opt <?= $defaultPlan === 'semanal' ? 'ic-plan-opt-active' : '' ?>"
            data-plan="semanal" type="button">
      <div class="ic-plan-info">
        <span class="ic-plan-kicker">Cuota semanal</span>
        <span class="ic-plan-precio">$<?= fmt((float)$a['cuotas_sem_monto']) ?></span>
        <span class="ic-plan-detalle"><?= (int)$a['cuotas_sem_cant'] ?> sem · total $<?= fmt($totalSemanal) ?></span>
      </div>
      <div class="ic-radio <?= $defaultPlan === 'semanal' ? 'ic-radio-checked' : '' ?>"></div>
    </button>
    <?php if ($tieneContado): ?>
    <div class="ic-plan-divider"></div>
    <?php endif; ?>
    <?php endif; ?>

    <?php if ($tieneContado): ?>
    <button class="ic-plan-opt <?= $defaultPlan === 'contado' ? 'ic-plan-opt-active' : '' ?>"
            data-plan="contado" type="button">
      <div class="ic-plan-info">
        <span class="ic-plan-kicker">Contado</span>
        <span class="ic-plan-precio ic-plan-precio-md">$<?= fmt((float)$a['precio_contado']) ?></span>
      </div>
      <div class="ic-radio <?= $defaultPlan === 'contado' ? 'ic-radio-checked' : '' ?>"></div>
    </button>
    <?php endif; ?>

  </div>
  <?php endif; ?>

  <!-- BULLETS DE CONFIANZA -->
  <ul class="ic-trust">
    <li>✓ Entrega en Tucumán, Sgo. del Estero y Catamarca</li>
    <li>✓ Crédito en el acto con DNI</li>
  </ul>

  <!-- CTAs -->
  <a id="btnConsultar"
     href="<?= htmlspecialchars($waUrlDefault, ENT_QUOTES, 'UTF-8') ?>"
     target="_blank" rel="noopener noreferrer"
     class="ic-btn-consultar">
    <svg width="20" height="20" viewBox="0 0 32 32" fill="currentColor" style="margin-right:8px;flex-shrink:0;">
      <path d="M16 2C8.27 2 2 8.27 2 16c0 2.44.66 4.82 1.9 6.9L2 30l7.34-1.87A13.94 13.94 0 0 0 16 30c7.73 0 14-6.27 14-14S23.73 2 16 2zm7.6 19.4c-.32.9-1.87 1.72-2.58 1.82-.66.1-1.5.14-2.42-.15-.56-.18-1.28-.42-2.2-.82-3.88-1.68-6.42-5.6-6.62-5.86-.2-.26-1.6-2.13-1.6-4.06 0-1.93 1.01-2.88 1.37-3.27.36-.39.78-.49 1.04-.49.26 0 .52 0 .75.01.24.01.56-.09.88.67.32.78 1.1 2.7 1.2 2.9.1.2.16.43.03.69-.13.26-.2.42-.39.65-.2.23-.41.51-.59.69-.19.18-.39.38-.17.74.22.36.99 1.63 2.13 2.64 1.46 1.3 2.69 1.7 3.05 1.89.36.19.57.16.78-.1.21-.26.9-1.05 1.14-1.41.24-.36.48-.3.81-.18.33.12 2.1.99 2.46 1.17.36.18.6.27.69.42.09.16.09.9-.23 1.8z"/>
    </svg>
    Consultar por este producto
  </a>
  <p class="ic-cta-note">Abre la línea de consultas de la empresa con el mensaje ya armado.</p>

  <button id="btnCompartir" class="ic-btn-compartir" type="button">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;">
      <path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8M16 6l-4-4-4 4M12 2v13"/>
    </svg>
    Compartir producto
  </button>
  <p class="ic-cta-note">Para vendedores: compartí el enlace por tu propio WhatsApp.</p>

  <button id="btnAgregar" class="ic-btn-agregar" type="button">
    + Agregar a Mi consulta
  </button>

</div>

<script>
const IC_PROD = {
  id: <?= (int)$a['id'] ?>,
  nombre: <?= json_encode($a['nombre']) ?>,
  imagen: <?= json_encode(!empty($a['imagen']) ? UPLOAD_URL . $a['imagen'] : '') ?>,
  cuotas_sem_cant: <?= (int)($a['cuotas_sem_cant'] ?? 0) ?>,
  cuotas_sem_monto: <?= (float)($a['cuotas_sem_monto'] ?? 0) ?>,
  precio_contado: <?= (float)($a['precio_contado'] ?? 0) ?>,
  catSlug: <?= json_encode($catSlug) ?>,
  waUrlSemanal: <?= json_encode($waUrlSemanal) ?>,
  waUrlContado: <?= json_encode($waUrlContado) ?>,
  selectedPlan: <?= json_encode($defaultPlan) ?>,
  pageUrl: <?= json_encode($pageUrl) ?>
};
</script>
<script src="assets/js/ic.js" defer></script>
</body>
</html>
