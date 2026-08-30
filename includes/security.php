<?php
/**
 * Funciones de seguridad compartidas por toda la tienda.
 */

function iniciar_sesion_segura() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,   // JS no puede leer la cookie de sesión
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

// Token CSRF: evita que otra web pueda enviar formularios en tu nombre
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verificar($token) {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

function limpiar($valor) {
    return htmlspecialchars(trim($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

// --- Protección del panel de administrador ---
function requerir_admin() {
    iniciar_sesion_segura();
    if (empty($_SESSION['admin_id'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

// --- Protección de clientes ---
function requerir_login_cliente() {
    iniciar_sesion_segura();
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

// Limitar intentos de login (defensa básica contra fuerza bruta)
function demasiados_intentos($clave_sesion, $max = 5, $ventana_segundos = 300) {
    iniciar_sesion_segura();
    $ahora = time();
    if (empty($_SESSION[$clave_sesion])) {
        $_SESSION[$clave_sesion] = [];
    }
    // limpia intentos viejos
    $_SESSION[$clave_sesion] = array_filter($_SESSION[$clave_sesion], fn($t) => $t > $ahora - $ventana_segundos);
    return count($_SESSION[$clave_sesion]) >= $max;
}

function registrar_intento($clave_sesion) {
    $_SESSION[$clave_sesion][] = time();
}
