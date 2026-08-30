<?php
require_once __DIR__ . '/config/db.php';
$tituloPagina = 'Crear cuenta';
require_once __DIR__ . '/includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verificar($_POST['csrf'] ?? '')) {
        $error = 'Token inválido, recarga la página.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $pass = $_POST['password'] ?? '';

        if (!$email) {
            $error = 'Correo no válido.';
        } elseif (strlen($pass) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } else {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $error = 'Ya existe una cuenta con ese correo.';
            } else {
                $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?,?,?)");
                $stmt->execute([limpiar($name), $email, $hash]);
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                header('Location: /index.php');
                exit;
            }
        }
    }
}
?>
<div style="max-width:380px;margin:0 auto;">
  <h1 style="font-size:20px;">Crear cuenta</h1>
  <?php if ($error): ?><p style="color:#ef4444;"><?= limpiar($error) ?></p><?php endif; ?>
  <form method="POST">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <label style="display:block;font-size:12px;color:var(--muted);margin:10px 0 4px;">Nombre</label>
    <input type="text" name="name" required style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--border);background:var(--bg);color:#fff;">
    <label style="display:block;font-size:12px;color:var(--muted);margin:10px 0 4px;">Correo</label>
    <input type="email" name="email" required style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--border);background:var(--bg);color:#fff;">
    <label style="display:block;font-size:12px;color:var(--muted);margin:10px 0 4px;">Contraseña</label>
    <input type="password" name="password" required minlength="8" style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--border);background:var(--bg);color:#fff;">
    <button type="submit" class="btn" style="margin-top:16px;width:100%;">Crear cuenta</button>
  </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
