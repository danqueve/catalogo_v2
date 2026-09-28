<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';

use Config\Promociones;
use Helpers\Auth;

Auth::requiereAdmin();

$mensaje = '';
$tipoMensaje = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validarCsrf($_POST['csrf_token'] ?? '')) {
        $mensaje = 'Token CSRF inválido. Actualizá la página e intentá nuevamente.';
        $tipoMensaje = 'danger';
    } else {
        $accion = $_POST['accion'] ?? '';

        try {
            if ($accion === 'subir') {
                if (empty($_FILES['imagen']) || ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    throw new RuntimeException('Seleccioná una imagen para la promoción.');
                }

                $orden = filter_var($_POST['orden'] ?? 1, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1, 'max_range' => 999],
                ]);
                if ($orden === false) {
                    throw new RuntimeException('El orden debe ser un número entre 1 y 999.');
                }

                Promociones::subir($_FILES['imagen'], $orden);
                $mensaje = 'Promoción cargada. Ya está disponible en el carrusel.';
            } elseif ($accion === 'eliminar') {
                Promociones::eliminar((string) ($_POST['archivo'] ?? ''));
                $mensaje = 'Promoción eliminada del carrusel.';
            }
        } catch (RuntimeException $e) {
            $mensaje = $e->getMessage();
            $tipoMensaje = 'danger';
        }
    }
}

$promociones = Promociones::obtenerParaAdmin();
$tituloAdmin = 'Promociones';
require 'partials/header.php';
?>

<div class="d-flex align-items-center gap-3 mb-1 flex-wrap">
  <h1 class="section-title mb-0">Promociones</h1>
</div>
<p class="section-subtitle mb-4">Cargá las imágenes que se mostrarán en el carrusel de la portada.</p>

<?php if ($mensaje): ?>
  <div class="alert-ios alert-ios-<?= $tipoMensaje ?> mb-3">
    <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<div class="card-ios p-3 p-md-4 mb-4">
  <h2 class="h6 fw-bold mb-2">Nueva imagen para el carrusel</h2>
  <p class="text-muted mb-3" style="font-size:.84rem;">
    Usá JPG, PNG o WebP de hasta 3 MB. Para mejor resultado, prepará la pieza horizontal en proporción 21:8.
  </p>
  <form method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
    <?= Auth::campoCSRF() ?>
    <input type="hidden" name="accion" value="subir">
    <div class="col-md-7">
      <label for="promoImagen" class="form-label fw-semibold" style="font-size:.85rem;">Imagen *</label>
      <input id="promoImagen" type="file" name="imagen" class="form-control form-control-ios"
             accept="image/jpeg,image/png,image/webp" required>
    </div>
    <div class="col-md-2">
      <label for="promoOrden" class="form-label fw-semibold" style="font-size:.85rem;">Orden</label>
      <input id="promoOrden" type="number" name="orden" min="1" max="999" step="1"
             value="<?= count($promociones) + 1 ?>" class="form-control form-control-ios" required>
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn-ios-primary w-100">Subir promoción</button>
    </div>
  </form>
</div>

<div class="d-flex align-items-center justify-content-between gap-3 mb-2">
  <h2 class="h6 fw-bold mb-0">Imágenes activas <span class="text-muted fw-normal">(<?= count($promociones) ?>)</span></h2>
  <a href="<?= htmlspecialchars(BASE_URL . '/promociones.php', ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="btn-ios-secondary" style="text-decoration:none;font-size:.82rem;">
    Ver página pública
  </a>
</div>

<?php if (empty($promociones)): ?>
  <div class="card-ios p-4 text-center text-muted" style="font-size:.9rem;">
    Todavía no hay imágenes. Al cargar la primera, aparecerá el carrusel en la portada.
  </div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($promociones as $indice => $promocion): ?>
      <div class="col-12 col-lg-6">
        <article class="card-ios overflow-hidden h-100">
          <img src="<?= htmlspecialchars($promocion['imagen'], ENT_QUOTES, 'UTF-8') ?>"
               alt="Promoción <?= $indice + 1 ?>" style="width:100%;aspect-ratio:21/8;object-fit:cover;display:block;">
          <div class="p-3 d-flex align-items-center gap-2">
            <div class="me-auto">
              <div class="fw-semibold" style="font-size:.87rem;">Promoción <?= $indice + 1 ?></div>
              <code style="font-size:.7rem;word-break:break-all;"><?= htmlspecialchars($promocion['nombre'], ENT_QUOTES, 'UTF-8') ?></code>
            </div>
            <form method="POST" class="m-0">
              <?= Auth::campoCSRF() ?>
              <input type="hidden" name="accion" value="eliminar">
              <input type="hidden" name="archivo" value="<?= htmlspecialchars($promocion['nombre'], ENT_QUOTES, 'UTF-8') ?>">
              <button type="submit" class="btn-ios-danger" style="padding:.38rem .72rem;font-size:.8rem;"
                      data-confirm="¿Eliminar esta promoción del carrusel?">Eliminar</button>
            </form>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require 'partials/footer.php'; ?>
