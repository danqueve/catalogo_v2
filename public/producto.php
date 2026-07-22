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

$pageUrl = BASE_URL . '/producto.php?id=' . $id;
$defaultPlan   = $tieneSemanal ? 'semanal' : 'contado';

$totalSemanal = $tieneSemanal
    ? (int)$a['cuotas_sem_cant'] * (float)$a['cuotas_sem_monto']
    : 0;

$catSlug   = $a['categoria_slug'] ?? '';
$catNombre = $a['categoria_nombre'] ?? 'Categoría';

$imageUrl = !empty($a['imagen']) ? UPLOAD_URL . rawurlencode($a['imagen']) : '';

$ogTitle = htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') . ' — Imperio Comercial';
$ogDesc  = !empty($a['descripcion'])
    ? htmlspecialchars(mb_strimwidth($a['descripcion'], 0, 140, '…'), ENT_QUOTES, 'UTF-8')
    : 'Ver precio y financiación en Imperio Comercial.';
$ogImage = $imageUrl ?: null;
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
<div>
  <?php if (!empty($a['imagen'])): ?>
    <a id="icProductImage" class="ic-ficha-img ic-ficha-img-zoom"
       href="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
       aria-label="Ver imagen ampliada de <?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?>">
      <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
           alt="<?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?>"
           loading="eager">
    </a>
  <?php else: ?>
    <div class="ic-ficha-img ic-ficha-img-ph">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
           stroke-width="1.2" opacity=".3">
        <rect x="3" y="3" width="18" height="18" rx="2"/>
        <circle cx="8.5" cy="8.5" r="1.5"/>
        <path d="M21 15l-5-5L5 21"/>
      </svg>
    </div>
  <?php endif; ?>
</div>

<?php if ($imageUrl): ?>
<div id="icImageLightbox" class="ic-image-lightbox" role="dialog" aria-modal="true"
     aria-label="Imagen ampliada del producto" aria-hidden="true" hidden>
  <div class="ic-image-lightbox-panel">
    <button id="icImageLightboxClose" class="ic-image-lightbox-close" type="button" aria-label="Cerrar imagen ampliada">×</button>
    <img src="<?= htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8') ?>"
         alt="<?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?>">
  </div>
</div>
<?php endif; ?>

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
  selectedPlan: <?= json_encode($defaultPlan) ?>,
  pageUrl: <?= json_encode($pageUrl) ?>
};
</script>
<script src="assets/js/ic.js" defer></script>
</body>
</html>
