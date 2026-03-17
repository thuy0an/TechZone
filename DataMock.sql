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

INSERT INTO brands (name, logo, created_at, updated_at) VALUES
('Lenovo', 'lenovo.png', NOW(), NOW()),
('HP', 'hp.png', NOW(), NOW()),
('Acer', 'acer.png', NOW(), NOW()),
('Xiaomi', 'xiaomi.png', NOW(), NOW()),
('Microsoft', 'microsoft.png', NOW(), NOW()),
('Huawei', 'huawei.png', NOW(), NOW()),
('JBL', 'jbl.png', NOW(), NOW()),
('Anker', 'anker.png', NOW(), NOW()),
('Logitech', 'logitech.png', NOW(), NOW()),
('MSI', 'msi.png', NOW(), NOW());

INSERT INTO suppliers (name, phone, email, address, created_at, updated_at) VALUES
('Synnex FPT', '02873000000', 'sales@synnexfpt.com', '152 Cach Mang Thang 8, Q3, TP.HCM', NOW(), NOW()),
('DGW', '02839420200', 'contact@dgw.vn', '59 Nguyen Du, Q1, TP.HCM', NOW(), NOW()),
('PSD', '02838228786', 'info@psd.com.vn', '294 Hoa Hao, Q10, TP.HCM', NOW(), NOW()),
('ASL', '02439763010', 'support@asl.com.vn', '84 Nguyen Ngoc Vu, Cau Giay, Ha Noi', NOW(), NOW()),
('Phuc Anh', '02462755777', 'sale@phucanh.com.vn', '15 Thai Ha, Dong Da, Ha Noi', NOW(), NOW()),
('An Phat', '02436210222', 'contact@anphat.com.vn', '49 Thai Ha, Dong Da, Ha Noi', NOW(), NOW());


INSERT INTO products 
(category_id, brand_id, code, name, image, description, unit, stock_quantity, import_price, profit_margin, selling_price, status, created_at, updated_at)
VALUES
(1,1,'IP15','iPhone 15 128GB','iphone15.jpg','Điện thoại Apple','Chiếc',20,20000000,0.15,23000000,'visible',NOW(),NOW()),
(2,3,'XPS13','Dell XPS 13','xps13.jpg','Laptop mỏng nhẹ','Chiếc',10,30000000,0.20,36000000,'visible',NOW(),NOW()),
(4,1,'AW9','Apple Watch Series 9','aw9.jpg','Smartwatch cao cấp','Chiếc',15,8000000,0.15,9200000,'visible',NOW(),NOW()),
(5,5,'SONYWH','Sony WH-1000XM5','sony.jpg','Tai nghe chống ồn','Cái',25,6000000,0.25,7500000,'visible',NOW(),NOW()),
(1,2,'S24','Samsung Galaxy S24','s24.jpg','Điện thoại Samsung','Chiếc',18,22000000,0.12,24640000,'visible',NOW(),NOW());

INSERT INTO products
(category_id, brand_id, code, name, image, description, unit, stock_quantity, import_price, profit_margin, selling_price, status, created_at, updated_at)
VALUES
(1,1,'IP14','iPhone 14 128GB','iphone14.jpg','Điện thoại Apple','Chiếc',22,17000000,0.15,19550000,'visible',NOW(),NOW()),
(1,2,'S23U','Samsung Galaxy S23 Ultra','s23ultra.jpg','Điện thoại Samsung','Chiếc',14,24000000,0.12,26880000,'visible',NOW(),NOW()),
(1,9,'XM14','Xiaomi 14 256GB','xiaomi14.jpg','Điện thoại Xiaomi','Chiếc',20,16000000,0.18,18880000,'visible',NOW(),NOW()),
(1,11,'HP60P','Huawei P60 Pro','huawei-p60.jpg','Điện thoại Huawei','Chiếc',12,18000000,0.14,20520000,'visible',NOW(),NOW()),
(2,6,'TPX1','Lenovo ThinkPad X1 Carbon','thinkpadx1.jpg','Laptop doanh nhân','Chiếc',8,32000000,0.18,37760000,'visible',NOW(),NOW()),
(2,7,'HPSX360','HP Spectre x360','spectre360.jpg','Laptop cao cấp','Chiếc',9,29000000,0.17,33930000,'visible',NOW(),NOW()),
(2,4,'ROGG14','Asus ROG Zephyrus G14','rog-g14.jpg','Laptop gaming','Chiếc',6,35000000,0.20,42000000,'visible',NOW(),NOW()),
(2,8,'SWIFT3','Acer Swift 3','swift3.jpg','Laptop mỏng nhẹ','Chiếc',16,18000000,0.16,20880000,'visible',NOW(),NOW()),
(2,15,'MSIK15','MSI Katana 15','msi-katana.jpg','Laptop gaming','Chiếc',7,27000000,0.19,32130000,'visible',NOW(),NOW()),
(3,1,'IPADA5','iPad Air 5 64GB','ipadair5.jpg','Tablet Apple','Chiếc',18,14000000,0.15,16100000,'visible',NOW(),NOW()),
(3,2,'TAB9','Samsung Galaxy Tab S9','tabs9.jpg','Tablet Samsung','Chiếc',12,19000000,0.12,21280000,'visible',NOW(),NOW()),
(3,9,'MIPAD6','Xiaomi Pad 6','mipad6.jpg','Tablet Xiaomi','Chiếc',20,9000000,0.18,10620000,'visible',NOW(),NOW()),
(3,10,'SRF9','Microsoft Surface Pro 9','surfacepro9.jpg','Tablet 2-in-1','Chiếc',9,22000000,0.17,25740000,'visible',NOW(),NOW()),
(4,2,'GW6','Samsung Galaxy Watch 6','gw6.jpg','Smartwatch Samsung','Chiếc',24,6000000,0.15,6900000,'visible',NOW(),NOW()),
(4,11,'HWGT4','Huawei Watch GT 4','watchgt4.jpg','Smartwatch Huawei','Chiếc',20,4500000,0.20,5400000,'visible',NOW(),NOW()),
(4,1,'AWSE2','Apple Watch SE 2','awse2.jpg','Smartwatch Apple','Chiếc',18,5000000,0.15,5750000,'visible',NOW(),NOW()),
(4,9,'XMWS3','Xiaomi Watch S3','xiaomiwatchs3.jpg','Smartwatch Xiaomi','Chiếc',26,3500000,0.18,4130000,'visible',NOW(),NOW()),
(5,12,'JBLF6','JBL Flip 6','jblflip6.jpg','Loa bluetooth','Cái',30,3000000,0.25,3750000,'visible',NOW(),NOW()),
(5,13,'ANKP20','Anker PowerCore 20000','anker20000.jpg','Pin sạc dự phòng','Cái',40,800000,0.30,1040000,'visible',NOW(),NOW()),
(5,14,'MXM3S','Logitech MX Master 3S','mxmaster3s.jpg','Chuột không dây','Cái',25,1800000,0.22,2196000,'visible',NOW(),NOW()),
(5,5,'WF1000','Sony WF-1000XM5','sony-wf1000xm5.jpg','Tai nghe true wireless','Cái',20,5000000,0.20,6000000,'visible',NOW(),NOW()),
(5,13,'ANK65','Anker 65W GaN Charger','anker-65w.jpg','Sạc nhanh USB-C','Cái',35,600000,0.28,768000,'visible',NOW(),NOW()),
(5,14,'C920','Logitech C920 Pro HD','c920.jpg','Webcam Full HD','Cái',22,1200000,0.20,1440000,'visible',NOW(),NOW()),
(2,7,'HPENV14','HP Envy 14','hp-envy14.jpg','Laptop sang trong','Chiếc',11,24000000,0.16,27840000,'visible',NOW(),NOW()),
(3,6,'TBP11','Lenovo Tab P11','tabp11.jpg','Tablet Lenovo','Chiếc',17,8000000,0.18,9440000,'visible',NOW(),NOW());

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