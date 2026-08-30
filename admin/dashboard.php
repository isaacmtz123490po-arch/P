<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
requerir_admin();

$mensaje = '';
$error = '';

// --- Crear producto ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_producto'])) {
    if (!csrf_verificar($_POST['csrf'] ?? '')) {
        $error = 'Token inválido, recarga la página.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category, stock) VALUES (?,?,?,?,?)");
        $stmt->execute([
            limpiar($_POST['name']),
            limpiar($_POST['description']),
            (float)$_POST['price'],
            limpiar($_POST['category']),
            (int)$_POST['stock'],
        ]);
        $mensaje = 'Producto creado.';
    }
}

// --- Subir imagen o video a un producto ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_media'])) {
    if (!csrf_verificar($_POST['csrf'] ?? '')) {
        $error = 'Token inválido.';
    } else {
        $productId = (int)$_POST['product_id'];
        $resultado = manejar_subida_media($productId, $pdo);
        if ($resultado['ok']) {
            $mensaje = $resultado['msg'];
        } else {
            $error = $resultado['msg'];
        }
    }
}

// --- Eliminar producto ---
if (isset($_GET['borrar_producto'])) {
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([(int)$_GET['borrar_producto']]);
    header('Location: /admin/dashboard.php');
    exit;
}

// --- Eliminar media ---
if (isset($_GET['borrar_media'])) {
    $stmt = $pdo->prepare("SELECT file_path FROM product_media WHERE id = ?");
    $stmt->execute([(int)$_GET['borrar_media']]);
    $m = $stmt->fetch();
    if ($m) {
        $ruta = __DIR__ . '/..' . $m['file_path'];
        if (file_exists($ruta)) unlink($ruta);
        $pdo->prepare("DELETE FROM product_media WHERE id = ?")->execute([(int)$_GET['borrar_media']]);
    }
    header('Location: /admin/dashboard.php');
    exit;
}

/**
 * Guarda un archivo de imagen o video subido y lo asocia a un producto.
 * Los videos NO se recomprimen: se guardan tal cual para conservar calidad HD/4K.
 */
function manejar_subida_media($productId, $pdo) {
    if (empty($_FILES['media']['name'])) {
        return ['ok' => false, 'msg' => 'No seleccionaste ningún archivo.'];
    }
    $archivo = $_FILES['media'];
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'msg' => 'Error al subir el archivo (código ' . $archivo['error'] . '). Si es un video 4K, revisa los límites en php.ini / .htaccess.'];
    }

    $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    $imagenes = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $videos = ['mp4', 'mov', 'webm', 'mkv'];

    if (in_array($ext, $imagenes)) {
        $tipo = 'image';
        $carpeta = '/assets/uploads/images/';
    } elseif (in_array($ext, $videos)) {
        $tipo = 'video';
        $carpeta = '/assets/uploads/videos/';
    } else {
        return ['ok' => false, 'msg' => 'Formato no permitido: .' . $ext];
    }

    $nombreSeguro = bin2hex(random_bytes(8)) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($archivo['name']));
    $rutaDestino = __DIR__ . '/..' . $carpeta . $nombreSeguro;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        return ['ok' => false, 'msg' => 'No se pudo guardar el archivo en el servidor.'];
    }

    $stmt = $pdo->prepare("INSERT INTO product_media (product_id, type, file_path) VALUES (?,?,?)");
    $stmt->execute([$productId, $tipo, $carpeta . $nombreSeguro]);

    return ['ok' => true, 'msg' => ucfirst($tipo) . ' subido correctamente.'];
}

// --- Cargar productos con su media para mostrarlos ---
$productos = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
$mediaStmt = $pdo->prepare("SELECT * FROM product_media WHERE product_id = ? ORDER BY sort_order, id");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel de administrador</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{font-family:system-ui,sans-serif;background:#0f0f1a;color:#f0f0ff;margin:0;}
.topbar{background:#1a1a2e;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;}
.topbar a{color:#ef4444;text-decoration:none;font-weight:600;font-size:13px;}
.wrap{max-width:900px;margin:0 auto;padding:24px;}
.card{background:#1a1a2e;border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:20px;margin-bottom:20px;}
h2{font-size:16px;margin-top:0;}
label{display:block;font-size:12px;color:#8a8ab0;margin:10px 0 4px;text-transform:uppercase;}
input,textarea{width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.15);background:#0f0f1a;color:#fff;box-sizing:border-box;}
button{margin-top:14px;padding:10px 18px;border:none;border-radius:8px;background:linear-gradient(135deg,#bd3193,#0891b2);color:#fff;font-weight:700;cursor:pointer;}
.msg{background:rgba(16,185,129,.15);color:#10b981;padding:10px;border-radius:8px;margin-bottom:14px;font-size:13px;}
.error{background:rgba(239,68,68,.15);color:#ef4444;padding:10px;border-radius:8px;margin-bottom:14px;font-size:13px;}
.producto{border-top:1px solid rgba(255,255,255,.1);padding-top:14px;margin-top:14px;}
.producto h3{margin:0 0 4px;font-size:15px;}
.producto .precio{color:#0891b2;font-weight:700;}
.media-list{display:flex;gap:10px;flex-wrap:wrap;margin:10px 0;}
.media-list img,.media-list video{width:90px;height:90px;object-fit:cover;border-radius:8px;}
.media-item{position:relative;}
.media-item a{position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;border-radius:50%;width:20px;height:20px;text-align:center;line-height:20px;font-size:12px;text-decoration:none;}
.borrar-prod{color:#ef4444;font-size:12px;text-decoration:none;}
small.hint{color:#8a8ab0;display:block;margin-top:6px;}
</style>
</head>
<body>
<div class="topbar">
  <strong>Panel de administrador — <?= limpiar($_SESSION['admin_user']) ?></strong>
  <a href="/admin/logout.php">Cerrar sesión</a>
</div>
<div class="wrap">

  <?php if ($mensaje): ?><div class="msg"><?= limpiar($mensaje) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="error"><?= limpiar($error) ?></div><?php endif; ?>

  <div class="card">
    <h2>➕ Nuevo producto</h2>
    <form method="POST">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <label>Nombre</label>
      <input type="text" name="name" required>
      <label>Descripción</label>
      <textarea name="description" rows="3"></textarea>
      <label>Precio</label>
      <input type="number" step="0.01" name="price" required>
      <label>Categoría (ropa, dispositivos, accesorios...)</label>
      <input type="text" name="category">
      <label>Stock</label>
      <input type="number" name="stock" value="0">
      <button type="submit" name="crear_producto">Crear producto</button>
    </form>
  </div>

  <div class="card">
    <h2>📦 Productos (<?= count($productos) ?>)</h2>
    <?php foreach ($productos as $p): ?>
      <div class="producto">
        <h3><?= limpiar($p['name']) ?> <span class="precio">$<?= number_format($p['price'],2) ?></span></h3>
        <p style="color:#8a8ab0;font-size:13px;"><?= limpiar($p['description']) ?></p>

        <div class="media-list">
          <?php
          $mediaStmt->execute([$p['id']]);
          foreach ($mediaStmt->fetchAll() as $m):
          ?>
            <div class="media-item">
              <?php if ($m['type'] === 'image'): ?>
                <img src="<?= limpiar($m['file_path']) ?>">
              <?php else: ?>
                <video src="<?= limpiar($m['file_path']) ?>" muted></video>
              <?php endif; ?>
              <a href="?borrar_media=<?= $m['id'] ?>" onclick="return confirm('¿Borrar este archivo?')">×</a>
            </div>
          <?php endforeach; ?>
        </div>

        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
          <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
          <input type="file" name="media" accept="image/*,video/*" required>
          <button type="submit" name="subir_media">Subir imagen / video</button>
          <small class="hint">Los videos se guardan sin comprimir para mantener calidad HD/4K. Los archivos grandes pueden tardar en subir.</small>
        </form>

        <p style="margin-top:10px;"><a class="borrar-prod" href="?borrar_producto=<?= $p['id'] ?>" onclick="return confirm('¿Borrar este producto y todos sus archivos?')">Borrar producto</a></p>
      </div>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>
