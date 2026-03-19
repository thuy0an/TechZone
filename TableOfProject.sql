-- =============================
-- CREATE DATABASE
-- =============================
DROP DATABASE IF EXISTS techzone_db;
CREATE DATABASE techzone_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE techzone_db;

-- =============================
-- ADMINS
-- =============================
CREATE TABLE admins (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  remember_token VARCHAR(255),
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================
-- USERS
-- =============================
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  remember_token VARCHAR(255),
  is_locked BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================
-- USER ADDRESSES
-- =============================
CREATE TABLE user_addresses (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  receiver_name VARCHAR(255),
  receiver_phone VARCHAR(20),
  address TEXT NOT NULL,
  is_default BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  -- Các cột lưu vết địa chỉ hành chính (GHN)
  `province_id` INT NULL,
  `district_id` INT NULL,
  `ward_code` VARCHAR(20) NULL,
  `province_name` VARCHAR(255) NULL,
  `district_name` VARCHAR(255) NULL,
  `ward_name` VARCHAR(255) NULL
);

-- =============================
-- CATEGORIES
-- =============================
CREATE TABLE categories (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================
-- BRANDS
-- =============================
CREATE TABLE brands (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  logo VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================
-- SUPPLIERS
-- =============================
CREATE TABLE suppliers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  email VARCHAR(255),
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================
-- PRODUCTS
-- =============================
CREATE TABLE products (
  id INT PRIMARY KEY AUTO_INCREMENT,
  category_id INT NOT NULL,
  brand_id INT NOT NULL,
  code VARCHAR(100) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  image VARCHAR(255),
  description TEXT,
  unit VARCHAR(50),
  initial_quantity INT DEFAULT 0,
  stock_quantity INT DEFAULT 0,
  import_price DECIMAL(15,2) DEFAULT 0,
  profit_margin DOUBLE DEFAULT 0,
  selling_price DECIMAL(15,2) DEFAULT 0,
  status ENUM('visible','hidden') DEFAULT 'visible',
  low_stock_threshold INT DEFAULT 5,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id),
  FOREIGN KEY (brand_id) REFERENCES brands(id)
);

-- =============================
-- IMPORT NOTES
-- =============================
CREATE TABLE import_notes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  admin_id INT NOT NULL,
  supplier_id INT NULL,
  import_date DATETIME,
  status ENUM('pending','completed') DEFAULT 'pending',
  total_cost DECIMAL(15,2) DEFAULT 0,
  paid_amount DECIMAL(15,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES admins(id),
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

CREATE TABLE import_note_details (
  id INT PRIMARY KEY AUTO_INCREMENT,
  import_note_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  import_price DECIMAL(15,2) NOT NULL,
  FOREIGN KEY (import_note_id) REFERENCES import_notes(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- =============================
-- PRODUCT PRICE HISTORY
-- =============================
CREATE TABLE product_price_histories (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  import_note_id INT NULL,
  import_price DECIMAL(15,2) NOT NULL,
  profit_margin DOUBLE NOT NULL,
  selling_price DECIMAL(15,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (import_note_id) REFERENCES import_notes(id)
);

-- =============================
-- PROMOTIONS
-- =============================
CREATE TABLE promotions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  start_date DATETIME,
  end_date DATETIME,
  is_active BOOLEAN DEFAULT TRUE,
  type ENUM('discount_by_product','discount_bill') NOT NULL,
  discount_value DECIMAL(15,2) NOT NULL,
  discount_unit ENUM('percent','amount') NOT NULL,
  min_bill_value DECIMAL(15,2) DEFAULT 0,
  max_discount_amount DECIMAL(15,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE promotion_product (
  promotion_id INT,
  product_id INT,
  PRIMARY KEY (promotion_id, product_id),
  FOREIGN KEY (promotion_id) REFERENCES promotions(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- =============================
-- CARTS
-- =============================
CREATE TABLE carts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE cart_items (
  id INT PRIMARY KEY AUTO_INCREMENT,
  cart_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT DEFAULT 1,
  price_at_addition DECIMAL(15,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cart_id) REFERENCES carts(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- =============================
-- ORDERS
-- =============================
CREATE TABLE `orders` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `promotion_id` INT NULL,
  `order_code` VARCHAR(50) UNIQUE NOT NULL, -- Mã đơn hàng hiển thị (VD: ORD-20260318-001)
  `order_date` DATETIME,
  `status` ENUM('new','confirmed','shipping','delivered','completed','cancelled','failed') DEFAULT 'new',
  `shipping_address` TEXT NOT NULL,
  `receiver_name` VARCHAR(255),
  `receiver_phone` VARCHAR(20),
  `receiver_email` VARCHAR(255),
  
  -- Các cột lưu vết địa chỉ hành chính (GHN)
  `province_id` INT NULL,
  `district_id` INT NULL,
  `ward_code` VARCHAR(20) NULL,
  `province_name` VARCHAR(255) NULL,
  `district_name` VARCHAR(255) NULL,
  `ward_name` VARCHAR(255) NULL,
  
  `payment_method` ENUM('cash','bank_transfer','online') NOT NULL,
  `total_amount` DECIMAL(15,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE order_details (
  id INT PRIMARY KEY AUTO_INCREMENT,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(15,2) NOT NULL,
  discount_applied DECIMAL(15,2) DEFAULT 0,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE `import_note_payments` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `import_note_id` BIGINT UNSIGNED NOT NULL,
  `admin_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  FOREIGN KEY (`import_note_id`) REFERENCES `import_notes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`)
);