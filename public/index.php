<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';

use Models\Categoria;

$categoriaModel = new Categoria();
$categorias     = $categoriaModel->obtenerActivas();

$meses = ['enero','febrero','marzo','abril','mayo','junio',
          'julio','agosto','septiembre','octubre','noviembre','diciembre'];
$mesActual = $meses[(int)date('n') - 1];

$promoSlug = 'promos-del-mes';
$hayPromos = false;
foreach ($categorias as $cat) {
    if ($cat['slug'] === $promoSlug) { $hayPromos = true; break; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#ffffff">
  <title>Catálogo</title>
  <link rel="icon" type="image/png" href="assets/img/logo.png">
  <link rel="apple-touch-icon" href="assets/img/logo.png">
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">
  <link rel="stylesheet" href="assets/css/app.css">
  <style>
    .promo-banner-wrap{max-width:960px;margin:0 auto;padding:1rem .75rem .5rem}
    .promo-card{
      position:relative;
      border-radius:20px;
      background:linear-gradient(135deg,#FF6B00 0%,#FF3B30 100%);
      padding:2rem 1.5rem;
      text-align:center;
      color:#fff;
      box-shadow:0 8px 32px rgba(255,80,0,.38);
      cursor:pointer;
      overflow:hidden;
      transition:transform .2s cubic-bezier(.25,.46,.45,.94),box-shadow .2s cubic-bezier(.25,.46,.45,.94);
      text-decoration:none;
      display:block;
    }
    .promo-card:hover{transform:translateY(-3px);box-shadow:0 14px 40px rgba(255,80,0,.46);color:#fff}
    .promo-card:active{transform:scale(.98);color:#fff}
    .promo-card::before{
      content:'';
      position:absolute;inset:0;
      background:radial-gradient(ellipse at top right,rgba(255,255,255,.15) 0%,transparent 65%);
      pointer-events:none;
    }
    .promo-mes{
      font-size:.7rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;
      color:rgba(255,255,255,.75);margin:0 0 .4rem;
    }
    .promo-titulo{
      font-size:clamp(1.6rem,5vw,2.2rem);font-weight:900;
      letter-spacing:-.5px;line-height:1.1;margin:0 0 .35rem;color:#fff;
    }
    .promo-sub{
      font-size:.85rem;color:rgba(255,255,255,.82);
      margin:0 0 1.3rem;line-height:1.4;
    }
    .promo-btn{
      display:inline-flex;align-items:center;gap:.5rem;
      background:#fff;color:#D94800;
      border-radius:999px;padding:.6rem 1.6rem;
      font-size:.9rem;font-weight:700;
      box-shadow:0 2px 12px rgba(0,0,0,.18);
      transition:transform .15s ease,box-shadow .15s ease;
    }
    .promo-card:hover .promo-btn{transform:scale(1.05);box-shadow:0 4px 18px rgba(0,0,0,.22)}
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar-glass px-3 py-2 d-flex align-items-center justify-content-between">
  <span class="navbar-brand mb-0 d-flex align-items-center gap-2">
    <img src="assets/img/logo.png" alt="Logo" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
    Catálogo
  </span>
</nav>

<!-- Banner Promos -->
<div class="promo-banner-wrap">
  <a href="categoria.php?slug=<?= $promoSlug ?>" class="promo-card">
    <p class="promo-mes">Promociones · <?= ucfirst($mesActual) ?> <?= date('Y') ?></p>
    <h2 class="promo-titulo">Ofertas del Mes</h2>
    <p class="promo-sub">Cuotas especiales y descuentos en productos seleccionados</p>
    <span class="promo-btn">
      Ver ofertas
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 12h14M12 5l7 7-7 7"/>
      </svg>
    </span>
  </a>
</div>

<!-- Contenido -->
<main class="container-fluid px-3 py-4" style="max-width:960px;margin:0 auto;">

  <h1 class="section-title">Categorías</h1>
  <p class="section-subtitle">Seleccioná una categoría para ver los productos</p>

  <?php if (empty($categorias)): ?>
    <div class="empty-state">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <rect x="2" y="3" width="20" height="14" rx="2"/>
        <path d="M8 21h8M12 17v4"/>
      </svg>
      <p class="mt-2">Aún no hay categorías disponibles.</p>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($categorias as $cat): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <a href="categoria.php?slug=<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>"
             class="card-ios">
            <div class="ratio-4-5">
              <?php if (!empty($cat['imagen'])): ?>
                <img src="uploads/productos/<?= htmlspecialchars($cat['imagen'], ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                     loading="lazy">
              <?php else: ?>
                <!-- Placeholder degradado cuando no hay imagen -->
                <div style="width:100%;height:100%;
                            background:linear-gradient(135deg,#e8eaf6 0%,#c5cae9 100%);
                            display:flex;align-items:center;justify-content:center;">
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                       stroke="#9fa8da" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <path d="M21 15l-5-5L5 21"/>
                  </svg>
                </div>
              <?php endif; ?>
            </div>
            <div class="card-cat-body d-flex justify-content-between align-items-center">
              <span class="cat-name"><?= htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
              <span class="cat-arrow">›</span>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
