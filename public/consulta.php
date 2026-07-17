<?php
require_once __DIR__ . '/../src/bootstrap.php';

$waBase = WA_PHONE ? 'https://wa.me/' . WA_PHONE : 'https://wa.me/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#f5ead8">
  <title>Mi consulta — Imperio Comercial</title>
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
  <h1 class="ic-page-title">
    Mi consulta <span id="cartCount" style="font-size:16px;color:var(--ic-text-muted);"></span>
  </h1>
</header>

<!-- ITEMS (renderizados por ic.js) -->
<div id="cartItems" class="ic-consulta-list"></div>

<!-- FORMULARIO -->
<div class="ic-consulta-form-wrap">
  <div class="ic-consulta-form">
    <p class="ic-form-title">Tus datos para la consulta</p>
    <input id="inputNombre" class="ic-input" type="text"
           placeholder="Nombre y apellido" autocomplete="name">
    <input id="inputZona" class="ic-input" type="text"
           placeholder="Zona · ej. San Miguel de Tucumán">
    <div class="ic-chips" style="flex-wrap:wrap;">
      <button class="ic-chip ic-chip-active" data-pago="credito" type="button">Quiero crédito</button>
      <button class="ic-chip" data-pago="contado" type="button">Pago contado</button>
    </div>
  </div>
</div>

<!-- CTA -->
<div class="ic-consulta-footer">
  <button id="btnEnviar" class="ic-btn-consultar" type="button" disabled>
    <svg width="20" height="20" viewBox="0 0 32 32" fill="currentColor" style="margin-right:8px;flex-shrink:0;">
      <path d="M16 2C8.27 2 2 8.27 2 16c0 2.44.66 4.82 1.9 6.9L2 30l7.34-1.87A13.94 13.94 0 0 0 16 30c7.73 0 14-6.27 14-14S23.73 2 16 2zm7.6 19.4c-.32.9-1.87 1.72-2.58 1.82-.66.1-1.5.14-2.42-.15-.56-.18-1.28-.42-2.2-.82-3.88-1.68-6.42-5.6-6.62-5.86-.2-.26-1.6-2.13-1.6-4.06 0-1.93 1.01-2.88 1.37-3.27.36-.39.78-.49 1.04-.49.26 0 .52 0 .75.01.24.01.56-.09.88.67.32.78 1.1 2.7 1.2 2.9.1.2.16.43.03.69-.13.26-.2.42-.39.65-.2.23-.41.51-.59.69-.19.18-.39.38-.17.74.22.36.99 1.63 2.13 2.64 1.46 1.3 2.69 1.7 3.05 1.89.36.19.57.16.78-.1.21-.26.9-1.05 1.14-1.41.24-.36.48-.3.81-.18.33.12 2.1.99 2.46 1.17.36.18.6.27.69.42.09.16.09.9-.23 1.8z"/>
    </svg>
    Enviar consulta
  </button>
  <p class="ic-cta-note">
    Arma un solo mensaje con los productos, tu zona y la forma de pago,
    y lo envía a la línea de consultas de la empresa. Sin registro, sin checkout.
  </p>
</div>

<script>
const IC_CONSULTA = { waBase: <?= json_encode($waBase) ?> };
</script>
<script src="assets/js/ic.js" defer></script>
</body>
</html>
