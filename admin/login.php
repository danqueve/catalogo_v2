<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';

use Models\Usuario;
use Helpers\Auth;

if (Auth::esAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Auth::validarCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Token inválido. Recarga la página.';
    } else {
        $usuario = trim($_POST['usuario'] ?? '');
        $clave   = $_POST['clave'] ?? '';

        $usuarioModel = new Usuario();
        $fila = $usuarioModel->buscarPorUsuario($usuario);

        if ($fila && password_verify($clave, $fila['password_hash'])) {
            Auth::login((int)$fila['id'], $fila['nombre']);
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}

$csrf = Auth::generarCsrf();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Acceso Admin — Catálogo</title>
  <link rel="icon" type="image/png" href="../public/assets/img/logo.png">
  <link rel="apple-touch-icon" href="../public/assets/img/logo.png">
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">
  <link rel="stylesheet" href="../public/assets/css/app.css">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--bg);
    }
    .login-card {
      background: var(--surface);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-lg);
      padding: 2.5rem 2rem;
      width: 100%;
      max-width: 380px;
    }
    .login-logo {
      text-align: center;
      margin-bottom: 1rem;
    }
    .login-logo img {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      object-fit: cover;
      box-shadow: var(--shadow-md);
    }
    .login-title {
      font-size: 1.3rem;
      font-weight: 700;
      text-align: center;
      margin-bottom: .25rem;
      color: var(--text);
    }
    .login-sub {
      font-size: .85rem;
      text-align: center;
      color: var(--text-2);
      margin-bottom: 1.75rem;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-logo">
      <img src="../public/assets/img/logo.png" alt="Logo">
    </div>
    <h1 class="login-title">Panel Admin</h1>
    <p class="login-sub">Catálogo Autogestionable</p>

    <?php if ($error): ?>
      <div class="alert-ios alert-ios-danger mb-3"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

      <div class="mb-3">
        <label for="usuario" class="form-label fw-semibold" style="font-size:.85rem;">Usuario</label>
        <input type="text" id="usuario" name="usuario" class="form-control form-control-ios"
               autocomplete="username" required autofocus>
      </div>
      <div class="mb-4">
        <label for="clave" class="form-label fw-semibold" style="font-size:.85rem;">Contraseña</label>
        <input type="password" id="clave" name="clave" class="form-control form-control-ios"
               autocomplete="current-password" required>
      </div>
      <button type="submit" class="btn-ios-primary w-100">Iniciar sesión</button>
    </form>
  </div>
</body>
</html>
