<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';
use Helpers\Auth;
use Config\Database;

Auth::requiereAdmin();

$db = Database::get();
$totalCat = $db->query('SELECT COUNT(*) FROM categorias WHERE activo=1')->fetchColumn();
$totalArt = $db->query('SELECT COUNT(*) FROM articulos  WHERE activo=1')->fetchColumn();

$tituloAdmin = 'Dashboard';
require 'partials/header.php';
?>

<h1 class="section-title mb-1">Dashboard</h1>
<p class="section-subtitle">Bienvenido, <?= htmlspecialchars($_SESSION['admin_nombre'], ENT_QUOTES, 'UTF-8') ?>.</p>

<div class="row g-3 mt-1">
  <div class="col-6 col-md-4">
    <div class="card-ios p-3 text-center">
      <div style="font-size:2rem;font-weight:700;color:var(--accent);"><?= (int)$totalCat ?></div>
      <div style="font-size:.85rem;color:var(--text-2);">Categorías activas</div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="card-ios p-3 text-center">
      <div style="font-size:2rem;font-weight:700;color:var(--wa);"><?= (int)$totalArt ?></div>
      <div style="font-size:.85rem;color:var(--text-2);">Artículos activos</div>
    </div>
  </div>
</div>

<div class="d-flex gap-2 flex-wrap mt-4">
  <a href="categorias.php" class="btn-ios-primary" style="text-decoration:none;">
    + Nueva categoría
  </a>
  <a href="articulos.php" class="btn-ios-secondary" style="text-decoration:none;">
    + Nuevo artículo
  </a>
</div>

<?php require 'partials/footer.php'; ?>
