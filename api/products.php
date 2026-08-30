<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$productos = $pdo->query("SELECT * FROM products WHERE active = 1 ORDER BY created_at DESC")->fetchAll();
$mediaStmt = $pdo->prepare("SELECT type, file_path FROM product_media WHERE product_id = ? ORDER BY sort_order, id");

foreach ($productos as &$p) {
    $mediaStmt->execute([$p['id']]);
    $p['media'] = $mediaStmt->fetchAll();
}

echo json_encode(['ok' => true, 'products' => $productos]);
