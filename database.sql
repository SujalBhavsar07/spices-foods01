-- =====================================================
-- SPICES FOODS DATABASE SCHEMA & INITIAL SEED DATA
-- Core PHP 8.x + MySQL (Pure Veg Edition)
-- Database Name: spices_foods_db
-- =====================================================

DROP DATABASE IF EXISTS spices_foods_db;
CREATE DATABASE spices_foods_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE spices_foods_db;

-- =====================================================
-- 1. TABLE STRUCTURES
-- =====================================================

-- Admins Table (pluralized to fix error)
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users Table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  phone VARCHAR(20) NOT NULL,
  password VARCHAR(255) NOT NULL,
  address TEXT,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) UNIQUE NOT NULL,
  image VARCHAR(255) DEFAULT 'default_category.jpg',
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Subcategories Table
CREATE TABLE subcategories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Foods Table
CREATE TABLE foods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subcategory_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  stock INT DEFAULT 100,
  featured TINYINT(1) DEFAULT 0,
  image VARCHAR(255) DEFAULT 'default_food.jpg',
  is_available TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (subcategory_id) REFERENCES subcategories(id) ON DELETE CASCADE
);

-- Cart Table
CREATE TABLE cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  food_id INT NOT NULL,
  quantity INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_cart (user_id, food_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  customer_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  address TEXT NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  delivery_charge DECIMAL(10,2) DEFAULT 40,
  payment_method VARCHAR(50) DEFAULT 'Cash on Delivery',
  payment_status ENUM('Pending', 'Paid') DEFAULT 'Pending',
  status ENUM('Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled') DEFAULT 'Pending',
  order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order Items Table
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  food_id INT NOT NULL,
  food_name VARCHAR(150) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  quantity INT NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE
);

-- =====================================================
-- 2. INITIAL SEED DATA (2 FOODS PER SUBCATEGORY)
-- =====================================================

-- Default Admin (Username: admin | Password: password123)
INSERT INTO admins (full_name, username, email, password) VALUES
('System Administrator', 'admin', 'admin@spicesfoods.com',
'$2y$10$Akj5xHx9A6wRgPdkWDEydOONw5Mq4rIEoWHPeYMHR9YnQCZEtWLIK');

-- Categories
INSERT INTO categories (id, name, image, description) VALUES
(1, 'Indian Food', 'indian_food.jpg', 'Authentic Indian veg curries, biryanis, pulaos, and clay-oven breads.'),
(2, 'Chinese Food', 'chinese_food.jpg', 'Sizzling veg noodles, fried rice, Manchurian, and pan-Asian starters.');

-- Subcategories
INSERT INTO subcategories (id, category_id, name) VALUES
-- Indian Food
(1, 1, 'Biryani'),
(2, 1, 'Pulao'),
(3, 1, 'Paneer & Veg Gravies'),
-- Chinese Food
(4, 2, 'Noodles'),
(5, 2, 'Fried Rice'),
(6, 2, 'Manchurian');

-- Foods (2 per subcategory = 12 total)
INSERT INTO foods (id, subcategory_id, name, description, price, stock, featured, image, is_available) VALUES
-- Indian: Biryani
(1, 1, 'Veg Biryani', 'Aromatic basmati rice layered with vegetables and spices.', 210.00, 50, 1, 'veg_biryani.jpg', 1),
(2, 1, 'Paneer Biryani', 'Fragrant rice cooked with paneer cubes and herbs.', 240.00, 50, 1, 'paneer_biryani.jpg', 1),
-- Indian: Pulao
(3, 2, 'Veg Pulao', 'Lightly seasoned rice tossed with peas and veggies.', 180.00, 60, 1, 'veg_pulao.jpg', 1),
(4, 2, 'Paneer Pulao', 'Rice pilaf cooked with paneer cubes and spices.', 210.00, 50, 1, 'paneer_pulao.jpg', 1),
-- Indian: Gravies
(5, 3, 'Paneer Butter Masala', 'Paneer cubes simmered in rich tomato butter gravy.', 240.00, 60, 1, 'paneer_butter_masala.jpg', 1),
(6, 3, 'Kaju Curry', 'Cashews cooked in creamy onion tomato gravy.', 270.00, 45, 1, 'kaju_curry.jpg', 1),
-- Chinese: Noodles
(7, 4, 'Veg Hakka Noodles', 'Wok-tossed noodles with vegetables and soy sauce.', 180.00, 60, 1, 'hakka_noodles.jpg', 1),
(8, 4, 'Veg Schezwan Noodles', 'Spicy noodles in tangy Schezwan sauce.', 190.00, 40, 1, 'schezwan_noodles.jpg', 1),
-- Chinese: Fried Rice
(9, 5, 'Veg Fried Rice', 'Classic fried rice with chopped vegetables.', 170.00, 50, 1, 'veg_fried_rice.jpg', 1),
(10, 5, 'Veg Schezwan Fried Rice', 'Spicy fried rice with Schezwan sauce.', 190.00, 50, 1, 'schezwan_fried_rice.jpg', 1),
-- Chinese: Manchurian
(11, 6, 'Veg Manchurian Dry', 'Crispy vegetable dumplings in garlic soy glaze.', 210.00, 40, 1, 'veg_manchurian.jpg', 1),
(12, 6, 'Veg Manchurian Gravy', 'Vegetable balls simmered in Chinese gravy.', 220.00, 40, 1, 'veg_manchurian_gravy.jpg', 1);

-- =====================================================
-- END OF FILE
-- =====================================================
