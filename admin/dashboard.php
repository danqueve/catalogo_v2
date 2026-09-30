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

<div class="admin-hero mb-4">
  <div class="admin-hero-text">
    <p class="section-subtitle mb-1" style="margin-bottom:.15rem;">
      Hola, <?= htmlspecialchars($_SESSION['admin_nombre'], ENT_QUOTES, 'UTF-8') ?>
    </p>
    <h1 class="section-title mb-3">¿Qué cargamos hoy?</h1>
    <a href="articulos.php" class="btn-ios-primary" style="text-decoration:none;">
      + Nuevo artículo
    </a>
  </div>
  <div class="admin-hero-stats">
    <div class="admin-hero-stat">
      <div class="admin-hero-stat-num"><?= (int)$totalArt ?></div>
      <div class="admin-hero-stat-label">Artículos</div>
    </div>
    <div class="admin-hero-stat">
      <div class="admin-hero-stat-num"><?= (int)$totalCat ?></div>
      <div class="admin-hero-stat-label">Categorías</div>
    </div>
  </div>
</div>

<div class="d-flex gap-2 flex-wrap">
  <a href="categorias.php" class="btn-ios-secondary" style="text-decoration:none;">
    + Nueva categoría
  </a>
  <a href="promociones.php" class="btn-ios-secondary" style="text-decoration:none;">
    Administrar promociones
  </a>
</div>

<?php require 'partials/footer.php'; ?>
