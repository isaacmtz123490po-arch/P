<?php
require_once __DIR__ . '/security.php';
iniciar_sesion_segura();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($tituloPagina) ? limpiar($tituloPagina) . ' - ' : '' ?>Mi Tienda</title>
<style>
:root{--bg:#0f0f1a;--card:#1a1a2e;--text:#f0f0ff;--muted:#8a8ab0;--accent:#bd3193;--accent2:#0891b2;--border:rgba(255,255,255,.1);}
*{box-sizing:border-box;}
body{font-family:system-ui,sans-serif;background:var(--bg);color:var(--text);margin:0;}
.nav{background:var(--card);padding:16px 24px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:10;border-bottom:1px solid var(--border);}
.nav a{color:var(--text);text-decoration:none;font-size:14px;margin-left:16px;}
.nav .brand{font-weight:800;font-size:18px;}
.wrap{max-width:1100px;margin:0 auto;padding:24px;}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px;}
.card{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;}
.card img,.card video{width:100%;height:200px;object-fit:cover;display:block;}
.card .info{padding:14px;}
.card h3{margin:0 0 6px;font-size:15px;}
.card .precio{color:var(--accent2);font-weight:700;}
.btn{display:inline-block;padding:10px 16px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;text-decoration:none;font-weight:700;font-size:13px;border:none;cursor:pointer;}
</style>
</head>
<body>
<div class="nav">
  <span class="brand">🛍️ Mi Tienda</span>
  <div>
    <a href="/index.php">Inicio</a>
    <a href="/checkout.php">Carrito</a>
    <?php if (!empty($_SESSION['user_id'])): ?>
      <a href="/logout.php">Cerrar sesión</a>
    <?php else: ?>
      <a href="/login.php">Entrar</a>
      <a href="/register.php">Crear cuenta</a>
    <?php endif; ?>
  </div>
</div>
<div class="wrap">
