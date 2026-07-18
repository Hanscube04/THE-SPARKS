-- ============================================================
-- THE SPARKS - Computer Sales, Maintenance & Repair System
-- Database Schema (Third Normal Form - 3NF)
-- CBE - BIT 2 - Internet and Web Development
-- ============================================================

CREATE DATABASE IF NOT EXISTS thesparks_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE thesparks_db;

-- ------------------------------------------------------------
-- 1. USERS TABLE (Customers - self register)
-- ------------------------------------------------------------
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone_encrypted VARCHAR(255) NOT NULL,      -- AES-256-CBC encrypted
    address_encrypted VARCHAR(500) DEFAULT NULL, -- AES-256-CBC encrypted
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active','suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. ADMINS TABLE (Admin / Super Admin - NOT self-registered)
--    Only a super_admin can create admin accounts.
-- ------------------------------------------------------------
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone_encrypted VARCHAR(255) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','super_admin') NOT NULL DEFAULT 'admin',
    created_by INT DEFAULT NULL,                 -- admin_id of the super_admin who created this account
    status ENUM('active','disabled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_admin_creator FOREIGN KEY (created_by) REFERENCES admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. CATEGORIES TABLE
-- ------------------------------------------------------------
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. PRODUCTS TABLE (computers, parts, accessories)
-- ------------------------------------------------------------
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    description TEXT,
    specifications TEXT,
    price DECIMAL(12,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    image_path VARCHAR(255) DEFAULT NULL,
    added_by INT DEFAULT NULL,                    -- admin_id
    status ENUM('available','out_of_stock','discontinued') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(category_id),
    CONSTRAINT fk_product_admin FOREIGN KEY (added_by) REFERENCES admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. ORDERS TABLE (sales)
-- ------------------------------------------------------------
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('pending','confirmed','dispatched','completed','cancelled') NOT NULL DEFAULT 'pending',
    handled_by INT DEFAULT NULL,                  -- admin_id who processed it
    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT fk_order_admin FOREIGN KEY (handled_by) REFERENCES admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. ORDER_ITEMS TABLE (line items - normalizes many-to-many orders<->products)
-- ------------------------------------------------------------
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,            -- price at time of sale (historical accuracy)
    CONSTRAINT fk_item_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_item_product FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. REPAIR_REQUESTS TABLE (maintenance & repair service)
-- ------------------------------------------------------------
CREATE TABLE repair_requests (
    repair_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_type VARCHAR(100) NOT NULL,
    brand_model VARCHAR(150) DEFAULT NULL,
    issue_description TEXT NOT NULL,
    estimated_cost DECIMAL(12,2) DEFAULT NULL,
    final_cost DECIMAL(12,2) DEFAULT NULL,
    technician_id INT DEFAULT NULL,                -- admin_id assigned
    status ENUM('submitted','diagnosing','in_progress','awaiting_parts','completed','cancelled') NOT NULL DEFAULT 'submitted',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_repair_user FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT fk_repair_tech FOREIGN KEY (technician_id) REFERENCES admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. REPAIR_STATUS_HISTORY (audit trail - normalizes repeated status updates)
-- ------------------------------------------------------------
CREATE TABLE repair_status_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    repair_id INT NOT NULL,
    status ENUM('submitted','diagnosing','in_progress','awaiting_parts','completed','cancelled') NOT NULL,
    notes VARCHAR(500) DEFAULT NULL,
    updated_by INT DEFAULT NULL,                   -- admin_id
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_history_repair FOREIGN KEY (repair_id) REFERENCES repair_requests(repair_id) ON DELETE CASCADE,
    CONSTRAINT fk_history_admin FOREIGN KEY (updated_by) REFERENCES admins(admin_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 9. PAYMENTS TABLE (covers both orders and repairs)
-- ------------------------------------------------------------
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT DEFAULT NULL,
    repair_id INT DEFAULT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash','mobile_money','bank_transfer','card') NOT NULL,
    payment_status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_payment_repair FOREIGN KEY (repair_id) REFERENCES repair_requests(repair_id) ON DELETE CASCADE,
    CONSTRAINT chk_payment_target CHECK (order_id IS NOT NULL OR repair_id IS NOT NULL)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 10. ACTIVITY_LOGS (system audit, like Hanscube IMS pattern)
-- ------------------------------------------------------------
CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    actor_type ENUM('user','admin','super_admin') NOT NULL,
    actor_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    details VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- SEED: default Super Admin (password should be changed after first login)
-- Password below = "SuperAdmin@2026" hashed with PHP password_hash (bcrypt)
-- Generate your own hash via classes/Encryption or password_hash() before real use.
-- ------------------------------------------------------------
INSERT INTO categories (category_name, description) VALUES
('Laptops', 'New and refurbished laptops'),
('Desktops', 'Desktop computers and workstations'),
('Spare Parts', 'RAM, SSD, HDD, motherboards, etc.'),
('Accessories', 'Keyboards, mice, chargers, bags');
