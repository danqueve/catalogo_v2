<?php
require_once __DIR__ . '/../src/bootstrap.php';

use Config\Promociones;

$promociones = Promociones::obtenerActivas();
$cantidad    = count($promociones);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#155e63">
  <title>Promociones | Imperio Comercial Tucumán</title>
  <meta name="description" content="Conocé las promociones, cuotas y beneficios vigentes de Imperio Comercial Tucumán.">
  <link rel="icon" type="image/png" href="assets/img/logo.png">
  <link rel="apple-touch-icon" href="assets/img/logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Caprasimo&family=Figtree:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/app.css<?= asset_version(__DIR__ . '/assets/css/app.css') ?>">
  <?php include __DIR__ . '/partials/analytics.php'; ?>
</head>
<body class="ic-page">

<header class="ic-header-back" aria-label="Navegación principal">
  <a href="index.php" class="ic-back-btn" aria-label="Volver al catálogo">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
      <path d="m15 18-6-6 6-6"/>
    </svg>
  </a>
  <div class="ic-header-back-info"><p class="ic-page-title">Promociones</p></div>
</header>

<main class="ic-promos-page">
  <header class="ic-promos-hero">
    <p class="ic-promo-eyebrow">Imperio Comercial</p>
    <h1>Promociones vigentes</h1>
    <p>Conocé las propuestas disponibles en este momento.</p>
  </header>

  <?php if ($cantidad > 0): ?>
    <section class="ic-promos-list" aria-label="Promociones vigentes">
      <?php foreach ($promociones as $promocion): ?>
        <article id="<?= htmlspecialchars($promocion['id'], ENT_QUOTES, 'UTF-8') ?>" class="ic-promos-card">
          <img src="<?= htmlspecialchars($promocion['imagen'], ENT_QUOTES, 'UTF-8') ?>"
               alt="<?= htmlspecialchars($promocion['alt'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
        </article>
      <?php endforeach; ?>
    </section>
  <?php else: ?>
    <section class="ic-empty">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path d="M20 12v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8"/>
        <path d="M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 1 1 2.5-2.5V7Zm0 0h4.5a2.5 2.5 0 1 0-2.5-2.5V7Z"/>
      </svg>
      <p>Muy pronto vamos a publicar nuevas promociones.</p>
    </section>
  <?php endif; ?>
</main>

</body>
</html>
