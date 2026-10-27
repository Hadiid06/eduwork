CREATE DATABASE IF NOT EXISTS `sesi_9`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `sesi_9`;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  icon VARCHAR(20) NOT NULL DEFAULT '🛍️',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  image_url VARCHAR(500) NULL,
  visual VARCHAR(20) NOT NULL DEFAULT '🛍️',
  badge VARCHAR(40) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  INDEX idx_products_featured (is_featured, created_at),
  INDEX idx_products_category (category_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS carts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  session_key VARCHAR(128) NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_carts_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cart_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cart_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  stock_reserved TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cart_items_cart
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_items_product
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  UNIQUE KEY uq_cart_product (cart_id, product_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  customer_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(30) NOT NULL,
  shipping_address TEXT NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  shipping_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  status ENUM('pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled')
    NOT NULL DEFAULT 'pending',
  payment_status ENUM('unpaid', 'paid', 'refunded')
    NOT NULL DEFAULT 'unpaid',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_orders_user_created (user_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NULL,
  product_name VARCHAR(150) NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  total_price DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  INDEX idx_order_items_product (product_id)
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (id, name, icon) VALUES
  (1, 'Elektronik', '🎧'),
  (2, 'Fashion', '👕'),
  (3, 'Rumah & Dapur', '🏠'),
  (4, 'Kecantikan', '✨'),
  (5, 'Olahraga', '⚽'),
  (6, 'Aksesori', '⌚');

INSERT IGNORE INTO products
  (id, category_id, name, description, price, stock, visual, badge, is_featured)
VALUES
  (1, 1, 'Headphone Studio Pro', 'Suara jernih dan nyaman untuk menemani aktivitas harian.', 459000, 24, '🎧', 'Terlaris', 1),
  (2, 2, 'Daily Tote Bag', 'Tas praktis dengan desain minimalis untuk pergi ke mana saja.', 189000, 38, '👜', 'Pilihan kami', 1),
  (3, 3, 'Lampu Meja Awan', 'Lampu meja hangat untuk membuat ruang terasa lebih nyaman.', 275000, 16, '💡', 'Baru', 1),
  (4, 4, 'Glow Care Set', 'Paket perawatan sederhana untuk rutinitas harianmu.', 329000, 20, '🧴', 'Favorit', 1),
  (5, 5, 'Botol Minum Active', 'Botol minum ringan untuk menemani olahraga dan perjalanan.', 99000, 52, '🥤', NULL, 1),
  (6, 6, 'Jam Tangan Classic', 'Jam tangan bergaya simpel yang cocok untuk berbagai suasana.', 525000, 12, '⌚', 'Terbatas', 1),
  (7, 1, 'Speaker Mini Wave', 'Speaker ringkas dengan suara yang mengisi suasana.', 215000, 30, '🔊', NULL, 0),
  (8, 2, 'Sneakers Everyday', 'Sneakers kasual yang ringan dipakai sepanjang hari.', 399000, 18, '👟', 'Populer', 0);
