<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';
use Helpers\Auth;
use Helpers\Upload;
use Models\Categoria;

Auth::requiereAdmin();

$catModel = new Categoria();
$msg      = '';
$msgTipo  = 'success';
$editando = null;

/* ── Procesar POST ──────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validarCsrf($_POST['csrf_token'] ?? '')) {
        $msg = 'Token CSRF inválido.'; $msgTipo = 'danger';
    } else {
        $accion = $_POST['accion'] ?? '';

        /* Crear */
        if ($accion === 'crear') {
            $nombre = trim($_POST['nombre'] ?? '');
            $slug   = trim($_POST['slug']   ?? '') ?: Categoria::slugify($nombre);
            $fijo   = isset($_POST['fijo']) ? 1 : 0;
            $imagen = null;

            if (empty($nombre)) {
                $msg = 'El nombre es obligatorio.'; $msgTipo = 'danger';
            } else {
                try {
                    if (!empty($_FILES['imagen']['name'])) {
                        $imagen = Upload::imagen($_FILES['imagen'], UPLOAD_DIR);
                    }
                    $catModel->crear($nombre, $slug, $imagen, $fijo);
                    $msg = 'Categoría creada correctamente.';
                } catch (\RuntimeException $e) {
                    $msg = $e->getMessage(); $msgTipo = 'danger';
                }
            }
        }

        /* Actualizar */
        elseif ($accion === 'actualizar') {
            $id     = (int)($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $slug   = trim($_POST['slug']   ?? '') ?: Categoria::slugify($nombre);
            $activo = (int)($_POST['activo'] ?? 1);
            $fijo   = isset($_POST['fijo']) ? 1 : 0;
            $orden  = ($_POST['orden'] !== '' && $_POST['orden'] !== null) ? (int)$_POST['orden'] : null;
            $imagen = null;

            if (!$id || empty($nombre)) {
                $msg = 'Datos inválidos.'; $msgTipo = 'danger';
            } else {
                try {
                    if (!empty($_FILES['imagen']['name'])) {
                        // Borrar imagen anterior
                        $vieja = $catModel->obtenerPorId($id);
                        if ($vieja && $vieja['imagen']) Upload::borrar(UPLOAD_DIR, $vieja['imagen']);
                        $imagen = Upload::imagen($_FILES['imagen'], UPLOAD_DIR);
                    }
                    $catModel->actualizar($id, $nombre, $slug, $imagen, $activo, $fijo, $orden);
                    $msg = 'Categoría actualizada.';
                } catch (\RuntimeException $e) {
                    $msg = $e->getMessage(); $msgTipo = 'danger';
                }
            }
        }

        /* Eliminar */
        elseif ($accion === 'eliminar') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $vieja = $catModel->obtenerPorId($id);
                if ($vieja && $vieja['imagen']) Upload::borrar(UPLOAD_DIR, $vieja['imagen']);
                $catModel->eliminar($id);
                $msg = 'Categoría eliminada.';
            }
        }

        /* Mover arriba */
        elseif ($accion === 'mover_arriba') {
            $id = (int)($_POST['id'] ?? 0);
            $idPrev = (int)($_POST['id_prev'] ?? 0);
            if ($id && $idPrev) {
                $catModel->intercambiarOrden($id, $idPrev);
                $msg = 'Orden actualizado.';
            }
        }

        /* Mover abajo */
        elseif ($accion === 'mover_abajo') {
            $id = (int)($_POST['id'] ?? 0);
            $idNext = (int)($_POST['id_next'] ?? 0);
            if ($id && $idNext) {
                $catModel->intercambiarOrden($id, $idNext);
                $msg = 'Orden actualizado.';
            }
        }
    }
}

/* ── Editar: pre-cargar datos ───────────────────────── */
if (isset($_GET['editar'])) {
    $editando = $catModel->obtenerPorId((int)$_GET['editar']);
}

$categorias  = $catModel->obtenerTodas();
$tituloAdmin = 'Categorías';
require 'partials/header.php';
?>

<div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
  <h1 class="section-title mb-0">Categorías</h1>
</div>

<?php if ($msg): ?>
  <div class="alert-ios alert-ios-<?= $msgTipo ?> mb-3">
    <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<!-- Formulario crear / editar -->
<div class="card-ios p-3 p-md-4 mb-4">
  <h2 class="h6 fw-bold mb-3"><?= $editando ? 'Editar categoría' : 'Nueva categoría' ?></h2>

  <form method="POST" enctype="multipart/form-data" novalidate>
    <?= Auth::campoCSRF() ?>
    <input type="hidden" name="accion" value="<?= $editando ? 'actualizar' : 'crear' ?>">
    <?php if ($editando): ?>
      <input type="hidden" name="id" value="<?= (int)$editando['id'] ?>">
    <?php endif; ?>

    <div class="row g-3">
      <div class="col-md-5">
        <label class="form-label fw-semibold" style="font-size:.85rem;">Nombre *</label>
        <input type="text" id="nombreInput" name="nombre" class="form-control form-control-ios"
               value="<?= htmlspecialchars($editando['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               required placeholder="Ej: Heladeras">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold" style="font-size:.85rem;">Slug (URL)</label>
        <input type="text" id="slugInput" name="slug" class="form-control form-control-ios"
               value="<?= htmlspecialchars($editando['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               placeholder="ej: heladeras"
               <?= $editando ? 'data-manual="true"' : '' ?>>
        <small class="text-muted">Se genera automáticamente desde el nombre.</small>
      </div>
      <?php if ($editando): ?>
      <div class="col-md-3">
        <label class="form-label fw-semibold" style="font-size:.85rem;">Estado</label>
        <select name="activo" class="form-select form-control-ios">
          <option value="1" <?= $editando['activo'] ? 'selected' : '' ?>>Activa</option>
          <option value="0" <?= !$editando['activo'] ? 'selected' : '' ?>>Inactiva</option>
        </select>
      </div>
      <?php endif; ?>
      <div class="col-12">
        <div class="form-check mt-1">
          <input class="form-check-input" type="checkbox" name="fijo" id="fijoCheck" value="1"
                 <?= ($editando['fijo'] ?? 0) ? 'checked' : '' ?>>
          <label class="form-check-label fw-semibold" for="fijoCheck" style="font-size:.85rem;">
            Fijar en inicio
            <small class="text-muted fw-normal ms-1">— aparece destacada arriba del catálogo</small>
          </label>
        </div>
      </div>
      <?php if ($editando): ?>
      <div class="col-md-3">
        <label class="form-label fw-semibold" style="font-size:.85rem;">
          Orden de visualización
          <small class="text-muted fw-normal ms-1">— número más bajo aparece primero</small>
        </label>
        <input type="number" name="orden" class="form-control form-control-ios"
               min="1" step="1"
               value="<?= (int)($editando['orden'] ?? 0) ?>"
               placeholder="1">
      </div>
      <?php endif; ?>
      <div class="col-12">
        <label class="form-label fw-semibold" style="font-size:.85rem;">
          Imagen de portada (proporción 4:5 recomendada)
        </label>
        <input type="file" id="imagenInput" name="imagen"
               class="form-control form-control-ios" accept="image/jpeg,image/png,image/webp">
        <img id="imgPreview" src="" alt="Preview">
        <?php if ($editando && $editando['imagen']): ?>
          <div class="mt-2">
            <img src="../public/uploads/productos/<?= htmlspecialchars($editando['imagen'], ENT_QUOTES, 'UTF-8') ?>"
                 class="img-thumb" alt="Imagen actual">
            <small class="text-muted d-block mt-1">Imagen actual. Subir nueva para reemplazar.</small>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="d-flex gap-2 mt-3">
      <button type="submit" class="btn-ios-primary">
        <?= $editando ? 'Guardar cambios' : 'Crear categoría' ?>
      </button>
      <?php if ($editando): ?>
        <a href="categorias.php" class="btn-ios-secondary" style="text-decoration:none;">Cancelar</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Tabla de categorías -->
<div class="table-ios">
  <table class="table table-hover mb-0">
    <thead>
      <tr>
        <th style="width:60px;">Orden</th>
        <th>Imagen</th>
        <th>Nombre</th>
        <th>Slug</th>
        <th>Estado</th>
        <th>Fijo</th>
        <th style="width:160px;">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($categorias)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Aún no hay categorías.</td></tr>
      <?php else: ?>
        <?php foreach ($categorias as $i => $c): ?>
          <?php
            $prevId = ($i > 0) ? $categorias[$i - 1]['id'] : null;
            $nextId = ($i < count($categorias) - 1) ? $categorias[$i + 1]['id'] : null;
          ?>
          <tr>
            <td>
              <div class="d-flex flex-column align-items-center gap-1">
                <span class="fw-bold" style="font-size:.8rem;color:var(--text-2);"><?= (int)$c['orden'] ?></span>
                <div class="d-flex gap-1">
                  <?php if ($prevId): ?>
                    <form method="POST" class="m-0">
                      <?= Auth::campoCSRF() ?>
                      <input type="hidden" name="accion" value="mover_arriba">
                      <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                      <input type="hidden" name="id_prev" value="<?= (int)$prevId ?>">
                      <button type="submit" class="btn-order-arrow" title="Mover arriba">↑</button>
                    </form>
                  <?php else: ?>
                    <span class="btn-order-arrow btn-order-arrow--disabled">↑</span>
                  <?php endif; ?>
                  <?php if ($nextId): ?>
                    <form method="POST" class="m-0">
                      <?= Auth::campoCSRF() ?>
                      <input type="hidden" name="accion" value="mover_abajo">
                      <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                      <input type="hidden" name="id_next" value="<?= (int)$nextId ?>">
                      <button type="submit" class="btn-order-arrow" title="Mover abajo">↓</button>
                    </form>
                  <?php else: ?>
                    <span class="btn-order-arrow btn-order-arrow--disabled">↓</span>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td>
              <?php if ($c['imagen']): ?>
                <img src="../public/uploads/productos/<?= htmlspecialchars($c['imagen'], ENT_QUOTES, 'UTF-8') ?>"
                     class="img-thumb" alt="">
              <?php else: ?>
                <div class="img-thumb d-flex align-items-center justify-content-center"
                     style="background:var(--surface-2);color:var(--text-3);">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>
                  </svg>
                </div>
              <?php endif; ?>
            </td>
            <td class="fw-semibold"><?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><code style="font-size:.78rem;"><?= htmlspecialchars($c['slug'], ENT_QUOTES, 'UTF-8') ?></code></td>
            <td>
              <?php if ($c['activo']): ?>
                <span class="badge" style="background:rgba(52,199,89,.15);color:#1a7a34;
                      border-radius:var(--radius-pill);padding:.25rem .6rem;font-size:.75rem;">Activa</span>
              <?php else: ?>
                <span class="badge" style="background:rgba(255,59,48,.1);color:#c0392b;
                      border-radius:var(--radius-pill);padding:.25rem .6rem;font-size:.75rem;">Inactiva</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($c['fijo']): ?>
                <span class="badge" style="background:rgba(255,149,0,.15);color:#b35900;
                      border-radius:var(--radius-pill);padding:.25rem .6rem;font-size:.75rem;">📌 Fija</span>
              <?php else: ?>
                <span style="color:var(--text-3);font-size:.8rem;">—</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="d-flex gap-2">
                <a href="categorias.php?editar=<?= (int)$c['id'] ?>"
                   class="btn-ios-secondary" style="text-decoration:none;padding:.35rem .7rem;font-size:.8rem;">
                  Editar
                </a>
                <form method="POST" class="m-0">
                  <?= Auth::campoCSRF() ?>
                  <input type="hidden" name="accion" value="eliminar">
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <button type="submit" class="btn-ios-danger"
                          style="padding:.35rem .7rem;font-size:.8rem;"
                          data-confirm="¿Eliminar la categoría «<?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?>»? Se eliminarán todos sus artículos.">
                    Eliminar
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require 'partials/footer.php'; ?>
