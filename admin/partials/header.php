<?php
// Debe incluirse después de bootstrap.php
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $tituloAdmin ?? 'Admin' ?> — Catálogo Admin</title>
  <link rel="icon" type="image/png" href="../public/assets/img/logo.png">
  <link rel="apple-touch-icon" href="../public/assets/img/logo.png">
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">
  <link rel="stylesheet" href="../public/assets/css/app.css">
  <style>
    body { background: var(--bg); }
    .admin-layout { display:flex; min-height:100vh; }
    .admin-content { flex:1; overflow:hidden; }
    @media(max-width:767px){
      .admin-sidebar-wrap { display:none; }
      .admin-sidebar-wrap.show { display:block; position:fixed; inset:0; z-index:1050;
        background:rgba(0,0,0,.35); }
      .admin-sidebar { height:100vh; overflow-y:auto; width:240px; }
    }
  </style>
</head>
<body>

<!-- Navbar admin -->
<nav class="admin-navbar px-3 py-2 d-flex align-items-center gap-3" style="position:sticky;top:0;z-index:999;">
  <button class="btn btn-sm d-md-none" id="sidebarToggle" aria-label="Menú">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/>
      <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
  </button>
  <img src="../public/assets/img/logo.png" alt="Logo"
       style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
  <span class="fw-bold" style="font-size:.95rem;">Admin Catálogo</span>
  <span class="ms-auto text-muted" style="font-size:.8rem;">
    <?= htmlspecialchars($_SESSION['admin_nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>
  </span>
  <a href="logout.php" class="btn-ios-secondary" style="white-space:nowrap;text-decoration:none;
     border-radius:var(--radius-pill);padding:.4rem .9rem;font-size:.82rem;">
    Salir
  </a>
</nav>

<div class="admin-layout">

<!-- Sidebar -->
<div class="admin-sidebar-wrap" id="sidebarWrap">
  <aside class="admin-sidebar p-3" style="width:220px;flex-shrink:0;">
    <nav class="nav flex-column gap-1">
      <a href="dashboard.php"
         class="nav-link <?= $paginaActual === 'dashboard.php' ? 'active' : '' ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" class="me-2"><rect x="3" y="3" width="7" height="7" rx="1"/>
          <rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
          <rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        Dashboard
      </a>
      <a href="categorias.php"
         class="nav-link <?= $paginaActual === 'categorias.php' ? 'active' : '' ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" class="me-2"><path d="M4 6h16M4 12h16M4 18h7"/>
        </svg>
        Categorías
      </a>
      <a href="articulos.php"
         class="nav-link <?= $paginaActual === 'articulos.php' ? 'active' : '' ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" class="me-2"><rect x="3" y="3" width="18" height="18" rx="2"/>
          <circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>
        </svg>
        Artículos
      </a>
      <hr style="border-color:var(--border);">
      <a href="../public/index.php" target="_blank"
         class="nav-link" style="font-size:.82rem;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" class="me-2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
          <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
        </svg>
        Ver catálogo
      </a>
    </nav>
  </aside>
</div>

<!-- Main content wrapper -->
<div class="admin-content p-3 p-md-4">
<script>
  const toggle = document.getElementById('sidebarToggle');
  const wrap   = document.getElementById('sidebarWrap');
  if(toggle) toggle.addEventListener('click', () => wrap.classList.toggle('show'));
  if(wrap) wrap.addEventListener('click', e => { if(e.target===wrap) wrap.classList.remove('show'); });
</script>
