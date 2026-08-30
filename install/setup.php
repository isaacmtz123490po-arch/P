<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
iniciar_sesion_segura();

// --- Candado de un solo uso: si ya existe un admin, esta página se bloquea sola ---
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM admins");
$yaExisteAdmin = $stmt->fetch()['total'] > 0;

$error = '';
$exito = false;

if ($yaExisteAdmin) {
    // No revela nada más. Borra o renombra esta carpeta /install después de usarla.
    http_response_code(403);
    die('La instalación ya fue completada. Por seguridad, borra la carpeta /install de tu servidor.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verificar($_POST['csrf'] ?? '')) {
        $error = 'Token inválido, recarga la página.';
    } else {
        $usuario = trim($_POST['username'] ?? '');
        $pass1 = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';

        if (strlen($usuario) < 3) {
            $error = 'El usuario debe tener al menos 3 caracteres.';
        } elseif (strlen($pass1) < 10) {
            $error = 'La contraseña debe tener al menos 10 caracteres.';
        } elseif ($pass1 !== $pass2) {
            $error = 'Las contraseñas no coinciden.';
        } else {
            // password_hash usa BCRYPT: la contraseña real nunca se guarda, solo su hash
            $hash = password_hash($pass1, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$usuario, $hash]);
            $exito = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Configuración inicial - Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{font-family:system-ui,sans-serif;background:#0f0f1a;color:#f0f0ff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;}
.box{background:#1a1a2e;padding:32px;border-radius:16px;max-width:420px;width:100%;border:1px solid rgba(255,255,255,.1);}
h1{font-size:20px;margin-bottom:8px;}
p.sub{color:#8a8ab0;font-size:13px;margin-bottom:24px;}
label{display:block;font-size:12px;color:#8a8ab0;margin-bottom:6px;text-transform:uppercase;}
input{width:100%;padding:12px;border-radius:10px;border:1px solid rgba(255,255,255,.15);background:#0f0f1a;color:#fff;margin-bottom:16px;box-sizing:border-box;}
button{width:100%;padding:13px;border:none;border-radius:10px;background:linear-gradient(135deg,#bd3193,#0891b2);color:#fff;font-weight:700;cursor:pointer;}
.error{background:rgba(239,68,68,.15);color:#ef4444;padding:10px;border-radius:8px;margin-bottom:16px;font-size:13px;}
.exito{background:rgba(16,185,129,.15);color:#10b981;padding:16px;border-radius:8px;font-size:14px;line-height:1.5;}
</style>
</head>
<body>
<div class="box">
  <h1>🔒 Crear cuenta de administrador</h1>
  <p class="sub">Esta página solo funciona una vez. Elige tu usuario y contraseña — se guardan cifrados (bcrypt), nunca en texto plano.</p>

  <?php if ($exito): ?>
    <div class="exito">
      ✅ Cuenta creada correctamente.<br><br>
      <strong>Ahora borra o renombra la carpeta <code>/install</code> de tu servidor.</strong> Mientras exista, es un riesgo de seguridad.<br><br>
      Ya puedes entrar en <a href="/admin/login.php" style="color:#fff;">/admin/login.php</a>
    </div>
  <?php else: ?>
    <?php if ($error): ?><div class="error"><?= limpiar($error) ?></div><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <label>Usuario</label>
      <input type="text" name="username" required minlength="3" autocomplete="off">
      <label>Contraseña (mínimo 10 caracteres)</label>
      <input type="password" name="password" required minlength="10" autocomplete="new-password">
      <label>Repetir contraseña</label>
      <input type="password" name="password2" required minlength="10" autocomplete="new-password">
      <button type="submit">Crear cuenta de administrador</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
