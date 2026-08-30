<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion_segura();

if (!empty($_SESSION['admin_id'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verificar($_POST['csrf'] ?? '')) {
        $error = 'Token inválido, recarga la página.';
    } elseif (demasiados_intentos('admin_intentos')) {
        $error = 'Demasiados intentos fallidos. Espera unos minutos.';
    } else {
        $usuario = trim($_POST['username'] ?? '');
        $pass = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$usuario]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($pass, $admin['password_hash'])) {
            session_regenerate_id(true); // evita fijación de sesión
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];
            $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?")->execute([$admin['id']]);
            header('Location: /admin/dashboard.php');
            exit;
        } else {
            registrar_intento('admin_intentos');
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Acceso administrador</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{font-family:system-ui,sans-serif;background:#0f0f1a;color:#f0f0ff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;}
.box{background:#1a1a2e;padding:32px;border-radius:16px;max-width:380px;width:100%;border:1px solid rgba(255,255,255,.1);}
h1{font-size:20px;margin-bottom:24px;}
label{display:block;font-size:12px;color:#8a8ab0;margin-bottom:6px;text-transform:uppercase;}
input{width:100%;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,.15);background:#0f0f1a;color:#fff;margin-bottom:16px;box-sizing:border-box;}
button{width:100%;padding:13px;border:none;border-radius:10px;background:linear-gradient(135deg,#bd3193,#0891b2);color:#fff;font-weight:700;cursor:pointer;}
.error{background:rgba(239,68,68,.15);color:#ef4444;padding:10px;border-radius:8px;margin-bottom:16px;font-size:13px;}
</style>
</head>
<body>
<div class="box">
  <h1>🔒 Panel de administrador</h1>
  <?php if ($error): ?><div class="error"><?= limpiar($error) ?></div><?php endif; ?>
  <form method="POST">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <label>Usuario</label>
    <input type="text" name="username" required autocomplete="username">
    <label>Contraseña</label>
    <input type="password" name="password" required autocomplete="current-password">
    <button type="submit">Entrar</button>
  </form>
</div>
</body>
</html>
