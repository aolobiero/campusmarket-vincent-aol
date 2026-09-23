CREATE DATABASE IF NOT EXISTS campusmarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campusmarket;

CREATE TABLE IF NOT EXISTS listings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    category VARCHAR(80) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    condition_label VARCHAR(60) NOT NULL,
    location_label VARCHAR(80) NOT NULL,
    distance_label VARCHAR(40) NOT NULL,
    seller_name VARCHAR(100) NOT NULL,
    seller_initials VARCHAR(4) NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    status_label VARCHAR(60) NOT NULL,
    detail_label VARCHAR(100) NOT NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS verification_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(30) NOT NULL,
    code_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (phone),
    INDEX (expires_at)
);

INSERT INTO listings (title, category, price, condition_label, location_label, distance_label, seller_name, seller_initials, image_url, status_label, detail_label) VALUES
('Calculus & Physics bundle', 'Textbooks', 5850, 'Like new', 'Textbooks', '0.4 mi', 'Nadia M.', 'NM', 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=700&q=80', 'Like new', 'for 3 books'),
('Olive reading chair', 'Furniture', 7800, 'Good condition', 'Furniture', '0.8 mi', 'David K.', 'DK', 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=700&q=80', 'Pickup today', 'negotiable'),
('City commuter bike', 'Transport', 23500, 'Excellent', 'Transport', '1.2 mi', 'Joseph O.', 'JO', 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=700&q=80', 'Verified seller', 'excellent condition'),
('Noise-cancelling headphones', 'Electronics', 4200, 'Barely used', 'Electronics', '0.6 mi', 'Amina W.', 'AW', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=700&q=80', 'Popular', 'barely used'),
('Compact study desk', 'Furniture', 6500, 'Good condition', 'Furniture', '1.5 mi', 'Brian M.', 'BM', 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6b7?auto=format&fit=crop&w=700&q=80', 'Good condition', 'pickup only'),
('Classic denim jacket', 'Fashion', 2750, 'New arrival', 'Fashion', '0.9 mi', 'Sarah N.', 'SN', 'https://images.unsplash.com/photo-1543076447-215ad9ba6923?auto=format&fit=crop&w=700&q=80', 'New arrival', 'size medium'),
('Samsung Galaxy A54', 'Phones & gadgets', 24500, 'Good condition', 'Phones & gadgets', '0.7 mi', 'Kevin R.', 'KR', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=700&q=80', 'Popular', '128GB unlocked');
