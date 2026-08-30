<?php
require_once __DIR__ . '/config/db.php';
$tituloPagina = 'Entrar';
require_once __DIR__ . '/includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verificar($_POST['csrf'] ?? '')) {
        $error = 'Token inválido, recarga la página.';
    } elseif (demasiados_intentos('user_intentos')) {
        $error = 'Demasiados intentos. Espera unos minutos.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: /index.php');
            exit;
        } else {
            registrar_intento('user_intentos');
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}
?>
<div style="max-width:380px;margin:0 auto;">
  <h1 style="font-size:20px;">Entrar</h1>
  <?php if ($error): ?><p style="color:#ef4444;"><?= limpiar($error) ?></p><?php endif; ?>
  <form method="POST">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <label style="display:block;font-size:12px;color:var(--muted);margin:10px 0 4px;">Correo</label>
    <input type="email" name="email" required style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--border);background:var(--bg);color:#fff;">
    <label style="display:block;font-size:12px;color:var(--muted);margin:10px 0 4px;">Contraseña</label>
    <input type="password" name="password" required style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--border);background:var(--bg);color:#fff;">
    <button type="submit" class="btn" style="margin-top:16px;width:100%;">Entrar</button>
  </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
