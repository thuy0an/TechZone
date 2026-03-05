INSERT INTO admins (name, email, password, is_active, created_at, updated_at) VALUES
('Diệp Thụy An', '3122410001@techzone.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW()),
('Thái Tuấn', '3122410451@techzone.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW()),
('Nguyễn Tuấn Vũ', '3122410483@techzone.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW()),
('Nguyễn Hoàng Ngọc Phong', '3122410310@techzone.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW()),
('Admin Hệ Thống', 'admin@techzone.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW(), NOW());

INSERT INTO users (name, email, password, phone, is_locked, created_at, updated_at) VALUES
('Lê Minh Khách', 'khach1@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901111111', 0, NOW(), NOW()),
('Trần Thị Lan', 'khach2@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0902222222', 0, NOW(), NOW()),
('Phạm Quốc Huy', 'khach3@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0903333333', 0, NOW(), NOW()),
('Ngô Thanh Hà', 'khach4@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0904444444', 1, NOW(), NOW()),
('Võ Nhật Nam', 'khach5@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0905555555', 0, NOW(), NOW());

INSERT INTO user_addresses (user_id, receiver_name, receiver_phone, address, is_default, created_at, updated_at) VALUES
(1, 'Lê Minh Khách', '0901111111', '12 Lê Lợi, Q1, TP.HCM', 1, NOW(), NOW()),
(2, 'Trần Thị Lan', '0902222222', '45 Nguyễn Trãi, Q5, TP.HCM', 1, NOW(), NOW()),
(3, 'Phạm Quốc Huy', '0903333333', '78 Hùng Vương, Đà Nẵng', 1, NOW(), NOW()),
(4, 'Ngô Thanh Hà', '0904444444', '22 Cầu Giấy, Hà Nội', 1, NOW(), NOW()),
(5, 'Võ Nhật Nam', '0905555555', '90 Lý Thường Kiệt, TP.HCM', 1, NOW(), NOW());

INSERT INTO categories (name, created_at, updated_at) VALUES
('Điện thoại', NOW(), NOW()),
('Laptop', NOW(), NOW()),
('Tablet', NOW(), NOW()),
('Smartwatch', NOW(), NOW()),
('Phụ kiện', NOW(), NOW());


INSERT INTO brands (name, logo, created_at, updated_at) VALUES
('Apple', 'apple.png', NOW(), NOW()),
('Samsung', 'samsung.png', NOW(), NOW()),
('Dell', 'dell.png', NOW(), NOW()),
('Asus', 'asus.png', NOW(), NOW()),
('Sony', 'sony.png', NOW(), NOW());


INSERT INTO products 
(category_id, brand_id, code, name, image, description, unit, stock_quantity, import_price, profit_margin, selling_price, status, created_at, updated_at)
VALUES
(1,1,'IP15','iPhone 15 128GB','iphone15.jpg','Điện thoại Apple','Chiếc',20,20000000,0.15,23000000,'visible',NOW(),NOW()),
(2,3,'XPS13','Dell XPS 13','xps13.jpg','Laptop mỏng nhẹ','Chiếc',10,30000000,0.20,36000000,'visible',NOW(),NOW()),
(4,1,'AW9','Apple Watch Series 9','aw9.jpg','Smartwatch cao cấp','Chiếc',15,8000000,0.15,9200000,'visible',NOW(),NOW()),
(5,5,'SONYWH','Sony WH-1000XM5','sony.jpg','Tai nghe chống ồn','Cái',25,6000000,0.25,7500000,'visible',NOW(),NOW()),
(1,2,'S24','Samsung Galaxy S24','s24.jpg','Điện thoại Samsung','Chiếc',18,22000000,0.12,24640000,'visible',NOW(),NOW());

INSERT INTO promotions 
(name, start_date, end_date, type, discount_value, discount_unit, min_bill_value, max_discount_amount, created_at, updated_at)
VALUES
('Giảm 10% toàn đơn', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'discount_bill', 10, 'percent', 5000000, 1000000, NOW(), NOW()),
('Giảm 500k Laptop', NOW(), DATE_ADD(NOW(), INTERVAL 60 DAY), 'discount_by_product', 500000, 'amount', 0, 500000, NOW(), NOW());

INSERT INTO promotion_product (promotion_id, product_id) VALUES
(2,2);



INSERT INTO orders 
(user_id, promotion_id, order_date, status, shipping_address, receiver_name, receiver_phone, payment_method, total_amount, created_at, updated_at)
VALUES
(1,NULL,NOW(),'delivered','12 Lê Lợi, Q1','Lê Minh Khách','0901111111','cash',23000000,NOW(),NOW()),
(2,1,NOW(),'confirmed','45 Nguyễn Trãi, Q5','Trần Thị Lan','0902222222','online',22176000,NOW(),NOW()),
(3,2,NOW(),'new','78 Hùng Vương','Phạm Quốc Huy','0903333333','bank_transfer',35500000,NOW(),NOW());

INSERT INTO order_details (order_id, product_id, quantity, unit_price, discount_applied) VALUES
(1,1,1,23000000,0),
(2,5,1,24640000,2464000),
(3,2,1,36000000,500000);