<?php
require_once __DIR__ . '/config/db.php';
$tituloPagina = 'Producto';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND active = 1");
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    echo '<p>Producto no encontrado.</p>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$mediaStmt = $pdo->prepare("SELECT * FROM product_media WHERE product_id = ? ORDER BY sort_order, id");
$mediaStmt->execute([$id]);
$media = $mediaStmt->fetchAll();
?>

<div style="display:grid;grid-template-columns:1.2fr 1fr;gap:30px;">
  <div>
    <?php if ($media): $principal = $media[0]; ?>
      <div id="visor" style="border-radius:14px;overflow:hidden;background:#000;">
        <?php if ($principal['type'] === 'video'): ?>
          <video src="<?= limpiar($principal['file_path']) ?>" controls playsinline style="width:100%;max-height:520px;"></video>
        <?php else: ?>
          <img src="<?= limpiar($principal['file_path']) ?>" style="width:100%;max-height:520px;object-fit:contain;">
        <?php endif; ?>
      </div>
      <div style="display:flex;gap:10px;margin-top:10px;flex-wrap:wrap;">
        <?php foreach ($media as $m): ?>
          <?php if ($m['type'] === 'video'): ?>
            <video src="<?= limpiar($m['file_path']) ?>" muted style="width:70px;height:70px;object-fit:cover;border-radius:8px;cursor:pointer;"
                   onclick="document.getElementById('visor').innerHTML='<video src=&quot;<?= limpiar($m['file_path']) ?>&quot; controls playsinline style=&quot;width:100%;max-height:520px;&quot;></video>'"></video>
          <?php else: ?>
            <img src="<?= limpiar($m['file_path']) ?>" style="width:70px;height:70px;object-fit:cover;border-radius:8px;cursor:pointer;"
                 onclick="document.getElementById('visor').innerHTML='<img src=&quot;<?= limpiar($m['file_path']) ?>&quot; style=&quot;width:100%;max-height:520px;object-fit:contain;&quot;>'">
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div style="height:300px;background:var(--card);border-radius:14px;"></div>
    <?php endif; ?>
  </div>

  <div>
    <h1 style="font-size:24px;"><?= limpiar($producto['name']) ?></h1>
    <p style="color:var(--accent2);font-size:22px;font-weight:700;">$<?= number_format($producto['price'], 2) ?></p>
    <p style="color:var(--muted);"><?= nl2br(limpiar($producto['description'])) ?></p>
    <form method="POST" action="/checkout.php">
      <input type="hidden" name="product_id" value="<?= $producto['id'] ?>">
      <label style="display:block;font-size:12px;color:var(--muted);margin-bottom:6px;">Cantidad</label>
      <input type="number" name="quantity" value="1" min="1" style="width:80px;padding:8px;border-radius:8px;border:1px solid var(--border);background:var(--bg);color:#fff;margin-bottom:14px;">
      <br>
      <button type="submit" class="btn">Comprar ahora</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
