<?php
require_once __DIR__ . '/includes/security.php';
iniciar_sesion_segura();
$_SESSION = [];
session_destroy();
header('Location: /index.php');
exit;
