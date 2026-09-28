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
  <meta name="theme-color" content="#f2f2f7">
  <title>Promociones | Imperio Comercial Tucumán</title>
  <meta name="description" content="Conocé las promociones, cuotas y beneficios vigentes de Imperio Comercial Tucumán.">
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

<nav class="navbar-glass px-3 py-2 d-flex align-items-center gap-2" aria-label="Navegación principal">
  <a href="index.php" class="back-btn" aria-label="Volver al catálogo">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
      <path d="m15 18-6-6 6-6"/>
    </svg>
    Catálogo
  </a>
  <span class="ms-auto navbar-brand mb-0">Promociones</span>
</nav>

<main class="promociones-page">
  <header class="promociones-hero">
    <p class="promociones-hero__eyebrow">Imperio Comercial</p>
    <h1>Beneficios para elegir hoy</h1>
    <p>Cuotas, crédito y entregas pensados para que encontrar lo que necesitás sea más simple.</p>
  </header>

  <?php if ($cantidad > 0): ?>
    <section class="promociones-list" aria-label="Promociones vigentes">
      <?php foreach ($promociones as $promocion): ?>
        <article id="<?= htmlspecialchars($promocion['id'], ENT_QUOTES, 'UTF-8') ?>"
                 class="promocion-card promocion-card--<?= htmlspecialchars($promocion['tono'], ENT_QUOTES, 'UTF-8') ?>">
          <p class="promocion-card__eyebrow"><?= htmlspecialchars($promocion['etiqueta'], ENT_QUOTES, 'UTF-8') ?></p>
          <h2><?= htmlspecialchars($promocion['titulo'], ENT_QUOTES, 'UTF-8') ?></h2>
          <p class="promocion-card__description"><?= htmlspecialchars($promocion['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
          <a class="promocion-card__cta"
             href="<?= htmlspecialchars($promocion['enlace'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($promocion['cta'], ENT_QUOTES, 'UTF-8') ?>
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M5 12h14M13 6l6 6-6 6"/>
            </svg>
          </a>
        </article>
      <?php endforeach; ?>
    </section>
  <?php else: ?>
    <section class="empty-state promociones-empty">
      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path d="M20 12v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8"/>
        <path d="M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 1 1 2.5-2.5V7Zm0 0h4.5a2.5 2.5 0 1 0-2.5-2.5V7Z"/>
      </svg>
      <p class="mt-3 mb-0">Muy pronto vamos a publicar nuevas promociones.</p>
    </section>
  <?php endif; ?>
</main>

</body>
</html>
