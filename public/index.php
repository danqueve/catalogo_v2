<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Models\Categoria;
use Helpers\Whatsapp;

$categoriaModel = new Categoria();
$categorias     = $categoriaModel->obtenerActivas();
$fijas          = array_filter($categorias, fn($c) => !empty($c['fijo']));
$resto          = array_filter($categorias, fn($c) => empty($c['fijo']));

// Imagen OG: primera categoría con imagen (fija primero, sino cualquiera)
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
  <meta name="theme-color" content="#ffffff">
  <title>Catálogo — Imperio Comercial</title>
  <meta name="description"         content="Explorá nuestro catálogo de productos con los mejores precios y financiación.">
  <meta property="og:type"         content="website">
  <meta property="og:site_name"    content="Imperio Comercial">
  <meta property="og:title"        content="Catálogo — Imperio Comercial">
  <meta property="og:description"  content="Explorá nuestro catálogo de productos con los mejores precios y financiación.">
  <meta property="og:url"          content="<?= BASE_URL ?>">
  <?php if ($ogImage): ?>
  <meta property="og:image"        content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="Catálogo — Imperio Comercial">
  <meta name="twitter:description" content="Explorá nuestro catálogo de productos con los mejores precios y financiación.">
  <meta name="twitter:image"       content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
  <?php endif; ?>
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

    /* ── Categorías fijas (destacadas) ── */
    .fijas-wrap{max-width:960px;margin:0 auto;padding:.75rem .75rem 0}
    .fijas-grid{display:grid;gap:.75rem;
      grid-template-columns:repeat(auto-fill,minmax(220px,1fr));}
    .fija-card{
      position:relative;
      border-radius:16px;
      overflow:hidden;
      display:flex;align-items:flex-end;
      min-height:140px;
      text-decoration:none;
      box-shadow:0 4px 18px rgba(0,0,0,.18);
      transition:transform .2s cubic-bezier(.25,.46,.45,.94),box-shadow .2s;
    }
    .fija-card:hover{transform:translateY(-3px);box-shadow:0 10px 28px rgba(0,0,0,.26);}
    .fija-card:active{transform:scale(.98);}
    .fija-card-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;}
    .fija-card-overlay{
      position:absolute;inset:0;
      background:linear-gradient(to top,rgba(0,0,0,.68) 0%,rgba(0,0,0,.08) 60%,transparent 100%);
    }
    .fija-card-body{position:relative;z-index:1;padding:.75rem 1rem;width:100%;}
    .fija-card-nombre{color:#fff;font-weight:700;font-size:1rem;line-height:1.2;}
    .fija-card-arrow{
      display:inline-flex;align-items:center;gap:.35rem;
      background:rgba(255,255,255,.18);backdrop-filter:blur(6px);
      color:#fff;border-radius:999px;padding:.25rem .7rem;
      font-size:.78rem;font-weight:600;margin-top:.4rem;
    }
    /* placeholder sin imagen */
    .fija-card-placeholder{
      position:absolute;inset:0;
      background:linear-gradient(135deg,#FF6B00 0%,#FF3B30 100%);
    }

    /* ── Botón compartir WhatsApp en cards de categoría ── */
    .card-cat-wrap { position: relative; }
    .btn-wa-cat {
      position: absolute;
      top: 8px; right: 8px;
      width: 36px; height: 36px;
      border-radius: 50%;
      background: #25D366;
      color: #fff;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 2px 8px rgba(0,0,0,.25);
      z-index: 2;
      transition: transform .15s ease, box-shadow .15s ease;
      text-decoration: none;
    }
    .btn-wa-cat:hover {
      transform: scale(1.12);
      box-shadow: 0 4px 14px rgba(37,211,102,.45);
      color: #fff;
    }
    .btn-wa-cat:active { transform: scale(.95); }
  </style>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=GT-5D9RBG98"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'GT-5D9RBG98');
  </script>
</head>
<body>

<!-- Navbar -->
<nav class="navbar-glass px-3 py-2 d-flex align-items-center justify-content-between">
  <span class="navbar-brand mb-0 d-flex align-items-center gap-2">
    <img src="assets/img/logo.png" alt="Logo" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
    Catálogo
  </span>
</nav>

<!-- Categorías fijas / destacadas -->
<?php if (!empty($fijas)): ?>
<div class="fijas-wrap">
  <div class="fijas-grid">
    <?php foreach ($fijas as $cat): ?>
      <?php $waUrl = Whatsapp::urlCategoria($cat['nombre'], $cat['slug']); ?>
      <div style="position:relative;">
        <a href="categoria.php?slug=<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>"
           class="fija-card">
          <?php if (!empty($cat['imagen'])): ?>
            <img src="uploads/productos/<?= htmlspecialchars($cat['imagen'], ENT_QUOTES, 'UTF-8') ?>"
                 class="fija-card-img" alt="<?= htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                 loading="lazy">
          <?php else: ?>
            <div class="fija-card-placeholder"></div>
          <?php endif; ?>
          <div class="fija-card-overlay"></div>
          <div class="fija-card-body">
            <div class="fija-card-nombre"><?= htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="fija-card-arrow">
              Ver productos
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
        </a>
        <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>"
           class="btn-wa-cat" style="top:10px;right:10px;"
           target="_blank" rel="noopener noreferrer"
           title="Compartir por WhatsApp">
          <svg width="18" height="18" viewBox="0 0 32 32" fill="currentColor">
            <path d="M16 2C8.27 2 2 8.27 2 16c0 2.44.66 4.82 1.9 6.9L2 30l7.34-1.87A13.94 13.94 0 0 0 16 30c7.73 0 14-6.27 14-14S23.73 2 16 2zm7.6 19.4c-.32.9-1.87 1.72-2.58 1.82-.66.1-1.5.14-2.42-.15-.56-.18-1.28-.42-2.2-.82-3.88-1.68-6.42-5.6-6.62-5.86-.2-.26-1.6-2.13-1.6-4.06 0-1.93 1.01-2.88 1.37-3.27.36-.39.78-.49 1.04-.49.26 0 .52 0 .75.01.24.01.56-.09.88.67.32.78 1.1 2.7 1.2 2.9.1.2.16.43.03.69-.13.26-.2.42-.39.65-.2.23-.41.51-.59.69-.19.18-.39.38-.17.74.22.36.99 1.63 2.13 2.64 1.46 1.3 2.69 1.7 3.05 1.89.36.19.57.16.78-.1.21-.26.9-1.05 1.14-1.41.24-.36.48-.3.81-.18.33.12 2.1 .99 2.46 1.17.36.18.6.27.69.42.09.16.09.9-.23 1.8z"/>
          </svg>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Contenido -->
<main class="container-fluid px-3 py-4" style="max-width:960px;margin:0 auto;">

  <?php if (!empty($fijas) && !empty($resto)): ?>
    <h1 class="section-title">Todas las categorías</h1>
  <?php elseif (empty($fijas)): ?>
    <h1 class="section-title">Categorías</h1>
    <p class="section-subtitle">Seleccioná una categoría para ver los productos</p>
  <?php endif; ?>

  <?php $mostrar = !empty($fijas) ? $resto : $categorias; ?>
  <?php if (empty($mostrar) && empty($fijas)): ?>
    <div class="empty-state">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <rect x="2" y="3" width="20" height="14" rx="2"/>
        <path d="M8 21h8M12 17v4"/>
      </svg>
      <p class="mt-2">Aún no hay categorías disponibles.</p>
    </div>
  <?php elseif (!empty($mostrar)): ?>
    <div class="row g-3">
      <?php foreach ($mostrar as $cat): ?>
        <?php $waUrl = Whatsapp::urlCategoria($cat['nombre'], $cat['slug']); ?>
        <div class="col-6 col-md-4 col-lg-3">
          <div class="card-cat-wrap">
            <a href="categoria.php?slug=<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>"
               class="card-ios">
              <div class="ratio-4-5">
                <?php if (!empty($cat['imagen'])): ?>
                  <img src="uploads/productos/<?= htmlspecialchars($cat['imagen'], ENT_QUOTES, 'UTF-8') ?>"
                       alt="<?= htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                       loading="lazy">
                <?php else: ?>
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
            <a href="<?= htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8') ?>"
               class="btn-wa-cat"
               target="_blank" rel="noopener noreferrer"
               title="Compartir <?= htmlspecialchars($cat['nombre'], ENT_QUOTES, 'UTF-8') ?> por WhatsApp">
              <svg width="18" height="18" viewBox="0 0 32 32" fill="currentColor">
                <path d="M16 2C8.27 2 2 8.27 2 16c0 2.44.66 4.82 1.9 6.9L2 30l7.34-1.87A13.94 13.94 0 0 0 16 30c7.73 0 14-6.27 14-14S23.73 2 16 2zm7.6 19.4c-.32.9-1.87 1.72-2.58 1.82-.66.1-1.5.14-2.42-.15-.56-.18-1.28-.42-2.2-.82-3.88-1.68-6.42-5.6-6.62-5.86-.2-.26-1.6-2.13-1.6-4.06 0-1.93 1.01-2.88 1.37-3.27.36-.39.78-.49 1.04-.49.26 0 .52 0 .75.01.24.01.56-.09.88.67.32.78 1.1 2.7 1.2 2.9.1.2.16.43.03.69-.13.26-.2.42-.39.65-.2.23-.41.51-.59.69-.19.18-.39.38-.17.74.22.36.99 1.63 2.13 2.64 1.46 1.3 2.69 1.7 3.05 1.89.36.19.57.16.78-.1.21-.26.9-1.05 1.14-1.41.24-.36.48-.3.81-.18.33.12 2.1 .99 2.46 1.17.36.18.6.27.69.42.09.16.09.9-.23 1.8z"/>
              </svg>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
