<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';
use Helpers\Auth;
use Helpers\Upload;
use Models\Articulo;
use Models\Categoria;

Auth::requiereAdmin();

$artModel = new Articulo();
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

        $fn = function (string $key): ?float {
            $v = trim($_POST[$key] ?? '');
            return ($v !== '') ? (float)str_replace(',', '.', $v) : null;
        };
        $fi = function (string $key): ?int {
            $v = trim($_POST[$key] ?? '');
            return ($v !== '') ? (int)$v : null;
        };

        /* Crear */
        if ($accion === 'crear') {
            $catId  = (int)($_POST['categoria_id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');

            if (!$catId || empty($nombre)) {
                $msg = 'Nombre y categoría son obligatorios.'; $msgTipo = 'danger';
            } elseif (empty($_FILES['imagen']['name'])) {
                $msg = 'Debes subir una imagen.'; $msgTipo = 'danger';
            } else {
                try {
                    $imagen = Upload::imagen($_FILES['imagen'], UPLOAD_DIR);
                    $artModel->crear(
                        $catId, $nombre,
                        trim($_POST['descripcion'] ?? '') ?: null,
                        $imagen,
                        $fn('precio_contado'),
                        $fi('cuotas_sem_cant'), $fn('cuotas_sem_monto'),
                        $fi('cuotas_mes_cant'), $fn('cuotas_mes_monto'),
                        $fi('orden')
                    );
                    $msg = 'Artículo creado correctamente.';
                } catch (\RuntimeException $e) {
                    $msg = $e->getMessage(); $msgTipo = 'danger';
                }
            }
        }

        /* Actualizar */
        elseif ($accion === 'actualizar') {
            $id    = (int)($_POST['id'] ?? 0);
            $catId = (int)($_POST['categoria_id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $activo = (int)($_POST['activo'] ?? 1);
            $imagen = null;

            if (!$id || !$catId || empty($nombre)) {
                $msg = 'Datos inválidos.'; $msgTipo = 'danger';
            } else {
                try {
                    if (!empty($_FILES['imagen']['name'])) {
                        $viejo = $artModel->obtenerPorId($id);
                        if ($viejo && $viejo['imagen']) Upload::borrar(UPLOAD_DIR, $viejo['imagen']);
                        $imagen = Upload::imagen($_FILES['imagen'], UPLOAD_DIR);
                    }
                    $artModel->actualizar(
                        $id, $catId, $nombre,
                        trim($_POST['descripcion'] ?? '') ?: null,
                        $imagen,
                        $fn('precio_contado'),
                        $fi('cuotas_sem_cant'), $fn('cuotas_sem_monto'),
                        $fi('cuotas_mes_cant'), $fn('cuotas_mes_monto'),
                        $activo,
                        $fi('orden')
                    );
                    $msg = 'Artículo actualizado.';
                } catch (\RuntimeException $e) {
                    $msg = $e->getMessage(); $msgTipo = 'danger';
                }
            }
        }

        /* Eliminar */
        elseif ($accion === 'eliminar') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $viejo = $artModel->obtenerPorId($id);
                if ($viejo && $viejo['imagen']) Upload::borrar(UPLOAD_DIR, $viejo['imagen']);
                $artModel->eliminar($id);
                $msg = 'Artículo eliminado.';
            }
        }

        /* Mover arriba */
        elseif ($accion === 'mover_arriba') {
            $id     = (int)($_POST['id']      ?? 0);
            $idPrev = (int)($_POST['id_prev'] ?? 0);
            if ($id && $idPrev) {
                $artModel->intercambiarOrden($id, $idPrev);
                $msg = 'Orden actualizado.';
            }
        }

        /* Mover abajo */
        elseif ($accion === 'mover_abajo') {
            $id     = (int)($_POST['id']      ?? 0);
            $idNext = (int)($_POST['id_next'] ?? 0);
            if ($id && $idNext) {
                $artModel->intercambiarOrden($id, $idNext);
                $msg = 'Orden actualizado.';
            }
        }

        /* Guardar ordenes en lote */
        elseif ($accion === 'guardar_ordenes_lote') {
            $ordenes = $_POST['ordenes'] ?? [];
            foreach ($ordenes as $id => $val) {
                $artModel->actualizarOrden((int)$id, (int)$val);
            }
            $msg = 'Órdenes de artículos actualizados.';
        }
    }
}

/* ── Editar ─────────────────────────────────────────── */
if (isset($_GET['editar'])) {
    $editando = $artModel->obtenerPorId((int)$_GET['editar']);
}

$porPagina       = 10;
$pagina          = max(1, (int)($_GET['pag'] ?? 1));
$busqueda        = trim($_GET['q'] ?? '');
$filtroCategoria = (int)($_GET['cat'] ?? 0);
$total           = $artModel->contarTodos($filtroCategoria, $busqueda);
$totalPaginas    = max(1, (int)ceil($total / $porPagina));
$pagina          = min($pagina, $totalPaginas);
$offset          = ($pagina - 1) * $porPagina;
$articulos       = $artModel->obtenerPaginados($porPagina, $offset, $filtroCategoria, $busqueda);
$categorias      = $catModel->obtenerTodas();

$tituloAdmin = 'Artículos';
require 'partials/header.php';
?>

<div class="d-flex align-items-center gap-3 mb-3">
  <h1 class="section-title mb-0">Artículos</h1>
</div>

<?php if ($msg): ?>
  <div class="alert-ios alert-ios-<?= $msgTipo ?> mb-3">
    <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
  </div>
<?php endif; ?>

<!-- Formulario crear / editar -->
<div class="card-ios p-3 p-md-4 mb-4">
  <h2 class="h6 fw-bold mb-3"><?= $editando ? 'Editar artículo' : 'Nuevo artículo' ?></h2>

  <form method="POST" enctype="multipart/form-data" novalidate>
    <?= Auth::campoCSRF() ?>
    <input type="hidden" name="accion" value="<?= $editando ? 'actualizar' : 'crear' ?>">
    <?php if ($editando): ?>
      <input type="hidden" name="id" value="<?= (int)$editando['id'] ?>">
    <?php endif; ?>

    <div class="row g-3">
      <!-- Nombre -->
      <div class="col-md-6">
        <label class="form-label fw-semibold" style="font-size:.85rem;">Nombre del producto *</label>
        <input type="text" name="nombre" class="form-control form-control-ios"
               value="<?= htmlspecialchars($editando['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               required placeholder="Ej: Heladera Samsung 300L">
      </div>

      <!-- Categoría -->
      <div class="col-md-3">
        <label class="form-label fw-semibold" style="font-size:.85rem;">Categoría *</label>
        <select name="categoria_id" class="form-select form-control-ios" required>
          <option value="">Seleccionar...</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= (int)$c['id'] ?>"
              <?= (isset($editando['categoria_id']) && $editando['categoria_id'] == $c['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Estado (solo al editar) -->
      <?php if ($editando): ?>
      <div class="col-md-3">
        <label class="form-label fw-semibold" style="font-size:.85rem;">Estado</label>
        <select name="activo" class="form-select form-control-ios">
          <option value="1" <?= $editando['activo'] ? 'selected' : '' ?>>Activo</option>
          <option value="0" <?= !$editando['activo'] ? 'selected' : '' ?>>Inactivo</option>
        </select>
      </div>
      <?php endif; ?>

      <!-- Descripción -->
      <div class="col-12">
        <label class="form-label fw-semibold" style="font-size:.85rem;">Descripción (opcional)</label>
        <textarea name="descripcion" class="form-control form-control-ios" rows="2"
                  placeholder="Características breves del producto..."><?=
          htmlspecialchars($editando['descripcion'] ?? '', ENT_QUOTES, 'UTF-8')
        ?></textarea>
      </div>

      <!-- Imagen -->
      <div class="col-md-6">
        <label class="form-label fw-semibold" style="font-size:.85rem;">
          Imagen del producto <?= $editando ? '(opcional: subir para reemplazar)' : '*' ?>
          <span style="color:var(--text-3);font-weight:400;"> — proporción 4:5</span>
        </label>
        <input type="file" id="imagenInput" name="imagen"
               class="form-control form-control-ios" accept="image/jpeg,image/png,image/webp"
               <?= $editando ? '' : 'required' ?>>
        <img id="imgPreview" src="" alt="Preview">
        <?php if ($editando && $editando['imagen']): ?>
          <div class="mt-2 d-flex align-items-center gap-2">
            <img src="<?= htmlspecialchars(UPLOAD_URL . rawurlencode($editando['imagen']), ENT_QUOTES, 'UTF-8') ?>"
                 class="img-thumb" alt="Imagen actual">
            <small class="text-muted">Imagen actual</small>
          </div>
        <?php endif; ?>
      </div>

      <!-- Precio contado -->
      <div class="col-md-6">
        <label class="form-label fw-semibold" style="font-size:.85rem;">Precio contado (opcional)</label>
        <div class="input-group">
          <span class="input-group-text" style="border-radius:var(--radius-sm) 0 0 var(--radius-sm);
                font-size:.85rem;border-color:var(--border);">$</span>
          <input type="number" name="precio_contado" min="0" step="0.01"
                 class="form-control form-control-ios" style="border-radius:0 var(--radius-sm) var(--radius-sm) 0;"
                 value="<?= htmlspecialchars($editando['precio_contado'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                 placeholder="0">
        </div>
      </div>

      <!-- Orden de visualización -->
      <div class="col-md-6">
        <label class="form-label fw-semibold" style="font-size:.85rem;">
          Orden de visualización
          <small class="text-muted fw-normal ms-1">— número más bajo aparece primero</small>
        </label>
        <input type="number" name="orden" class="form-control form-control-ios"
               min="1" step="1"
               value="<?= $editando ? (int)$editando['orden'] : '' ?>"
               placeholder="<?= $editando ? '' : 'Ej: 1 (Dejar vacío para colocar al final)' ?>">
      </div>

      <!-- Cuotas Semanales -->
      <div class="col-12">
        <p class="fw-semibold mb-2" style="font-size:.85rem;">Cuotas semanales</p>
        <div class="row g-2">
          <div class="col-6 col-md-3">
            <label class="form-label text-muted" style="font-size:.78rem;">Cantidad de cuotas</label>
            <input type="number" name="cuotas_sem_cant" min="1" step="1"
                   class="form-control form-control-ios"
                   value="<?= htmlspecialchars($editando['cuotas_sem_cant'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="Ej: 12">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label text-muted" style="font-size:.78rem;">Monto por cuota ($)</label>
            <input type="number" name="cuotas_sem_monto" min="0" step="0.01"
                   class="form-control form-control-ios"
                   value="<?= htmlspecialchars($editando['cuotas_sem_monto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="Ej: 5000">
          </div>
        </div>
      </div>

      <!-- Cuotas Mensuales -->
      <div class="col-12">
        <p class="fw-semibold mb-2" style="font-size:.85rem;">Cuotas mensuales</p>
        <div class="row g-2">
          <div class="col-6 col-md-3">
            <label class="form-label text-muted" style="font-size:.78rem;">Cantidad de cuotas</label>
            <input type="number" name="cuotas_mes_cant" min="1" step="1"
                   class="form-control form-control-ios"
                   value="<?= htmlspecialchars($editando['cuotas_mes_cant'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="Ej: 6">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label text-muted" style="font-size:.78rem;">Monto por cuota ($)</label>
            <input type="number" name="cuotas_mes_monto" min="0" step="0.01"
                   class="form-control form-control-ios"
                   value="<?= htmlspecialchars($editando['cuotas_mes_monto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="Ej: 22000">
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn-ios-primary">
        <?= $editando ? 'Guardar cambios' : 'Crear artículo' ?>
      </button>
      <?php if ($editando): ?>
        <a href="articulos.php" class="btn-ios-secondary" style="text-decoration:none;">Cancelar</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Lista de artículos -->
<div class="card-ios p-3 p-md-4">

  <!-- Cabecera de sección -->
  <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <h2 class="h6 fw-bold mb-0 me-auto">
      Artículos
      <span class="text-muted fw-normal" style="font-size:.82rem;">(<?= $total ?> en total)</span>
    </h2>

    <!-- Filtros: búsqueda + categoría -->
    <form method="GET" class="d-flex flex-wrap gap-2" style="flex:1;min-width:240px;max-width:560px;">
      <?php if ($editando): ?>
        <input type="hidden" name="editar" value="<?= (int)$editando['id'] ?>">
      <?php endif; ?>
      <input type="search" name="q" value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>"
             class="form-control form-control-ios" style="flex:1;min-width:140px;"
             placeholder="Buscar por nombre…">
      <select name="cat" class="form-select form-control-ios" style="min-width:130px;max-width:180px;">
        <option value="0">Todas las categorías</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $filtroCategoria === (int)$c['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-ios-primary" style="white-space:nowrap;">Filtrar</button>
      <?php if ($busqueda || $filtroCategoria): ?>
        <a href="articulos.php" class="btn-ios-secondary" style="text-decoration:none;white-space:nowrap;">Limpiar</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Formulario oculto para guardar orden en lote -->
  <form id="loteForm" method="POST">
    <?= Auth::campoCSRF() ?>
    <input type="hidden" name="accion" value="guardar_ordenes_lote">
  </form>

  <div class="d-flex justify-content-between align-items-center mb-2">
    <h2 class="h6 fw-bold mb-0">Lista de Artículos</h2>
    <?php if ($filtroCategoria > 0 && !empty($articulos)): ?>
      <button type="submit" form="loteForm" class="btn-ios-primary btn-sm" style="padding:.4rem .9rem; font-size:.82rem;">Guardar Orden</button>
    <?php endif; ?>
  </div>

  <?php if (!$filtroCategoria): ?>
    <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center gap-2" style="font-size:.8rem; border-radius: 12px; background: rgba(0, 122, 255, 0.08); border: none; color: #0056b3;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
      </svg>
      <span>Para ordenar y reorganizar los artículos, por favor selecciona una categoría específica en el filtro.</span>
    </div>
  <?php endif; ?>

  <!-- Tabla -->
  <div style="overflow-x:auto;">
    <table class="table table-hover mb-0" style="min-width:600px;">
      <thead>
        <tr>
          <th style="width:<?= $filtroCategoria > 0 ? '90px' : '60px' ?>;">Orden</th>
          <th>Imagen</th>
          <th>Nombre</th>
          <th>Categoría</th>
          <th>Semanales</th>
          <th>Mensuales</th>
          <th>Estado</th>
          <th style="width:160px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($articulos)): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">Sin resultados.</td></tr>
        <?php else: ?>
          <?php foreach ($articulos as $i => $a): ?>
            <?php
              $prevId = ($i > 0) ? $articulos[$i - 1]['id'] : null;
              $nextId = ($i < count($articulos) - 1) ? $articulos[$i + 1]['id'] : null;
            ?>
            <tr>
              <td>
                <?php if ($filtroCategoria > 0): ?>
                  <div class="d-flex flex-column align-items-center gap-1">
                    <input type="number" form="loteForm" name="ordenes[<?= (int)$a['id'] ?>]" value="<?= (int)$a['orden'] ?>" 
                           class="form-control form-control-ios form-control-sm text-center" 
                           style="width:55px; padding:2px; font-size:.8rem; font-weight:bold; margin-bottom: 2px;"
                           min="1" step="1">
                    <div class="d-flex gap-1">
                      <?php if ($prevId): ?>
                        <form method="POST" class="m-0">
                          <?= Auth::campoCSRF() ?>
                          <input type="hidden" name="accion" value="mover_arriba">
                          <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
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
                          <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                          <input type="hidden" name="id_next" value="<?= (int)$nextId ?>">
                          <button type="submit" class="btn-order-arrow" title="Mover abajo">↓</button>
                        </form>
                      <?php else: ?>
                        <span class="btn-order-arrow btn-order-arrow--disabled">↓</span>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php else: ?>
                  <span class="fw-bold text-muted" style="font-size:.8rem;" title="Filtra por una categoría para ordenar"><?= (int)$a['orden'] ?></span>
                <?php endif; ?>
              </td>
              <td>
                <img src="<?= htmlspecialchars(UPLOAD_URL . rawurlencode($a['imagen']), ENT_QUOTES, 'UTF-8') ?>"
                     class="img-thumb" alt="">
              </td>
              <td class="fw-semibold"><?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
              <td style="font-size:.82rem;color:var(--text-2);">
                <?= htmlspecialchars($a['categoria_nombre'], ENT_QUOTES, 'UTF-8') ?>
              </td>
              <td style="font-size:.82rem;">
                <?php if ($a['cuotas_sem_cant']): ?>
                  <?= (int)$a['cuotas_sem_cant'] ?> × $<?= number_format((float)$a['cuotas_sem_monto'],0,',','.') ?>
                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
              </td>
              <td style="font-size:.82rem;">
                <?php if ($a['cuotas_mes_cant']): ?>
                  <?= (int)$a['cuotas_mes_cant'] ?> × $<?= number_format((float)$a['cuotas_mes_monto'],0,',','.') ?>
                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
              </td>
              <td>
                <?php if ($a['activo']): ?>
                  <span class="badge" style="background:rgba(52,199,89,.15);color:#1a7a34;
                        border-radius:var(--radius-pill);padding:.25rem .6rem;font-size:.75rem;">Activo</span>
                <?php else: ?>
                  <span class="badge" style="background:rgba(255,59,48,.1);color:#c0392b;
                        border-radius:var(--radius-pill);padding:.25rem .6rem;font-size:.75rem;">Inactivo</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex gap-2">
                  <a href="articulos.php?editar=<?= (int)$a['id'] ?>&pag=<?= $pagina ?>&q=<?= urlencode($busqueda) ?>&cat=<?= $filtroCategoria ?>"
                     class="btn-ios-secondary" style="text-decoration:none;padding:.35rem .7rem;font-size:.8rem;">
                    Editar
                  </a>
                  <form method="POST" class="m-0">
                    <?= Auth::campoCSRF() ?>
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                    <button type="submit" class="btn-ios-danger"
                            style="padding:.35rem .7rem;font-size:.8rem;"
                            data-confirm="¿Eliminar «<?= htmlspecialchars($a['nombre'], ENT_QUOTES, 'UTF-8') ?>»?">
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

  <!-- Paginación -->
  <?php if ($totalPaginas > 1): ?>
    <?php
      $qBase = http_build_query(array_filter(['q' => $busqueda, 'cat' => $filtroCategoria ?: null]));
      $qBase = $qBase ? "&$qBase" : '';
    ?>
    <nav class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2">
      <small class="text-muted">
        Página <?= $pagina ?> de <?= $totalPaginas ?>
        — mostrando <?= count($articulos) ?> de <?= $total ?> artículos
      </small>
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="?pag=<?= $pagina - 1 ?><?= $qBase ?>">‹ Ant.</a>
        </li>
        <?php for ($p = max(1, $pagina - 2); $p <= min($totalPaginas, $pagina + 2); $p++): ?>
          <li class="page-item <?= $p === $pagina ? 'active' : '' ?>">
            <a class="page-link" href="?pag=<?= $p ?><?= $qBase ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
          <a class="page-link" href="?pag=<?= $pagina + 1 ?><?= $qBase ?>">Sig. ›</a>
        </li>
      </ul>
    </nav>
  <?php endif; ?>

</div><!-- /card-ios lista -->

<?php require 'partials/footer.php'; ?>
