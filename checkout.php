<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/payments.php';
$tituloPagina = 'Pago';
require_once __DIR__ . '/includes/header.php';

$productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? $_GET['quantity'] ?? 1));

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND active = 1");
$stmt->execute([$productId]);
$producto = $stmt->fetch();

if (!$producto) {
    echo '<p>Selecciona un producto desde el catálogo primero.</p>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$total = $producto['price'] * $quantity;
$mensaje = '';

// --- Al confirmar el método de pago ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_pago'])) {
    if (!csrf_verificar($_POST['csrf'] ?? '')) {
        die('Token inválido.');
    }
    $metodo = $_POST['payment_method'];

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, payment_method, shipping_name, shipping_address) VALUES (?,?,?,?,?)");
    $stmt->execute([
        $_SESSION['user_id'] ?? null,
        $total,
        $metodo,
        limpiar($_POST['shipping_name'] ?? ''),
        limpiar($_POST['shipping_address'] ?? ''),
    ]);
    $orderId = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?,?,?,?)")
        ->execute([$orderId, $productId, $quantity, $producto['price']]);

    if ($metodo === 'manual') {
        $mensaje = "Pedido #$orderId creado. Para completar tu compra:\n\n" . MANUAL_PAYMENT_INFO;
    } elseif ($metodo === 'stripe') {
        if (!STRIPE_SECRET_KEY) {
            $mensaje = "Pedido #$orderId creado, pero Stripe todavía no está configurado (falta la clave en config/payments.php).";
        } else {
            // Crea una sesión de pago real en Stripe (API vía cURL, sin necesidad de composer)
            $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_USERPWD => STRIPE_SECRET_KEY . ':',
                CURLOPT_POSTFIELDS => http_build_query([
                    'mode' => 'payment',
                    'line_items[0][price_data][currency]' => 'usd',
                    'line_items[0][price_data][product_data][name]' => $producto['name'],
                    'line_items[0][price_data][unit_amount]' => (int)round($total * 100),
                    'line_items[0][quantity]' => 1,
                    'success_url' => 'https://TU-DOMINIO.com/gracias.php?order=' . $orderId,
                    'cancel_url' => 'https://TU-DOMINIO.com/checkout.php',
                ]),
            ]);
            $resp = json_decode(curl_exec($ch), true);
            curl_close($ch);
            if (!empty($resp['url'])) {
                header('Location: ' . $resp['url']);
                exit;
            }
            $mensaje = "No se pudo iniciar el pago con Stripe. Revisa tu clave en config/payments.php.";
        }
    } elseif ($metodo === 'paypal') {
        if (!PAYPAL_CLIENT_ID || !PAYPAL_SECRET) {
            $mensaje = "Pedido #$orderId creado, pero PayPal todavía no está configurado (faltan las claves en config/payments.php).";
        } else {
            $base = PAYPAL_MODE === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
            // 1. Token de acceso
            $ch = curl_init("$base/v1/oauth2/token");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_USERPWD => PAYPAL_CLIENT_ID . ':' . PAYPAL_SECRET,
                CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            ]);
            $token = json_decode(curl_exec($ch), true)['access_token'] ?? null;
            curl_close($ch);

            if ($token) {
                // 2. Crear la orden de pago
                $ch = curl_init("$base/v2/checkout/orders");
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => ["Authorization: Bearer $token", 'Content-Type: application/json'],
                    CURLOPT_POSTFIELDS => json_encode([
                        'intent' => 'CAPTURE',
                        'purchase_units' => [[
                            'amount' => ['currency_code' => 'USD', 'value' => number_format($total, 2, '.', '')]
                        ]],
                        'application_context' => [
                            'return_url' => 'https://TU-DOMINIO.com/gracias.php?order=' . $orderId,
                            'cancel_url' => 'https://TU-DOMINIO.com/checkout.php',
                        ],
                    ]),
                ]);
                $resp = json_decode(curl_exec($ch), true);
                curl_close($ch);
                $approve = array_filter($resp['links'] ?? [], fn($l) => $l['rel'] === 'approve');
                $approve = reset($approve);
                if ($approve) {
                    header('Location: ' . $approve['href']);
                    exit;
                }
            }
            $mensaje = "No se pudo iniciar el pago con PayPal. Revisa tus claves en config/payments.php.";
        }
    }
}
?>

<h1 style="font-size:20px;">Finalizar compra</h1>

<?php if ($mensaje): ?>
  <div class="card" style="padding:20px;white-space:pre-line;"><?= limpiar($mensaje) ?></div>
<?php else: ?>

  <div class="card" style="padding:20px;margin-bottom:20px;">
    <strong><?= limpiar($producto['name']) ?></strong> × <?= $quantity ?>
    <div style="color:var(--accent2);font-weight:700;font-size:18px;margin-top:6px;">Total: $<?= number_format($total, 2) ?></div>
  </div>

  <form method="POST" class="card" style="padding:20px;">
    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
    <input type="hidden" name="product_id" value="<?= $productId ?>">
    <input type="hidden" name="quantity" value="<?= $quantity ?>">

    <label style="display:block;font-size:12px;color:var(--muted);margin-bottom:4px;">Nombre para el envío</label>
    <input type="text" name="shipping_name" required style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--border);background:var(--bg);color:#fff;margin-bottom:14px;">

    <label style="display:block;font-size:12px;color:var(--muted);margin-bottom:4px;">Dirección de envío</label>
    <textarea name="shipping_address" required rows="3" style="width:100%;padding:10px;border-radius:8px;border:1px solid var(--border);background:var(--bg);color:#fff;margin-bottom:14px;"></textarea>

    <label style="display:block;font-size:12px;color:var(--muted);margin-bottom:8px;">Método de pago</label>
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px;">
      <label style="display:flex;gap:8px;align-items:center;"><input type="radio" name="payment_method" value="stripe" required> Tarjeta (Stripe)</label>
      <label style="display:flex;gap:8px;align-items:center;"><input type="radio" name="payment_method" value="paypal"> PayPal</label>
      <label style="display:flex;gap:8px;align-items:center;"><input type="radio" name="payment_method" value="manual"> Transferencia / pago manual</label>
    </div>

    <button type="submit" name="confirmar_pago" class="btn" style="width:100%;">Confirmar pedido</button>
  </form>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
