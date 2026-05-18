<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';

use Models\Categoria;

$categoriaModel = new Categoria();
$categorias     = $categoriaModel->obtenerActivas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#ffffff">
  <title>Catálogo</title>
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar-glass px-3 py-2 d-flex align-items-center justify-content-between">
  <span class="navbar-brand mb-0">🛒 Catálogo</span>
</nav>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmRkTz8hFpKkRUXbB1UoFiNf+R0"
        crossorigin="anonymous"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
