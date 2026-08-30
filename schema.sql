-- =========================================================
-- Esquema de base de datos - Tienda
-- Importa este archivo en phpMyAdmin (o tu gestor de MySQL)
-- =========================================================

CREATE DATABASE IF NOT EXISTS tienda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tienda;

-- Administradores (solo se crea uno desde /install/setup.php)
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Clientes
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Productos (ropa, dispositivos, cosas personales, etc.)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(80),
    stock INT DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Imágenes y videos de cada producto (varias por producto)
CREATE TABLE product_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    type ENUM('image','video') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Pedidos
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    guest_email VARCHAR(150) NULL,
    total DECIMAL(10,2) NOT NULL,
    payment_method ENUM('stripe','paypal','manual') NOT NULL,
    payment_status ENUM('pendiente','pagado','cancelado') DEFAULT 'pendiente',
    shipping_name VARCHAR(150),
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);
