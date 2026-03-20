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

INSERT INTO `user_addresses` 
(`user_id`, `receiver_name`, `receiver_phone`, `address`, `province_id`, `district_id`, `ward_code`, `province_name`, `district_name`, `ward_name`, `is_default`, `created_at`, `updated_at`) 
VALUES
(1, 'Lê Minh Khách', '0901111111', '12 Lê Lợi', 202, 1442, '20109', 'Hồ Chí Minh', 'Quận 1', 'Phường Bến Nghé', 1, NOW(), NOW()),
(2, 'Trần Thị Lan', '0902222222', '45 Nguyễn Trãi', 202, 1446, '20314', 'Hồ Chí Minh', 'Quận 5', 'Phường 14', 1, NOW(), NOW()),
(3, 'Phạm Quốc Huy', '0903333333', '78 Hùng Vương', 203, 1520, '3010', 'Đà Nẵng', 'Quận Hải Châu', 'Phường Thạch Thang', 1, NOW(), NOW()),
(4, 'Ngô Thanh Hà', '0904444444', '22 Cầu Giấy', 201, 1482, '1A01', 'Hà Nội', 'Quận Ba Đình', 'Phường Điện Biên', 1, NOW(), NOW()),
(5, 'Võ Nhật Nam', '0905555555', '90 Lý Thường Kiệt', 202, 1451, '20211', 'Hồ Chí Minh', 'Quận 10', 'Phường 14', 1, NOW(), NOW());

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



INSERT INTO `orders`
(user_id, promotion_id, order_code, order_date, status, shipping_address, receiver_name, receiver_phone, receiver_email, province_id, district_id, ward_code, province_name, district_name, ward_name, payment_method, total_amount, created_at, updated_at)
VALUES
(1,  NULL,'ORD-071','2026-01-22 09:00:00','completed',   '23 Nguyễn Huệ',           'Nguyễn Thị Mai',    '0911000001','mai.nguyen@gmail.com',    202,1442,'20107','Hồ Chí Minh','Quận 1',          'Phường Bến Nghé',      'cash',         26880000,'2026-01-22 09:00:00','2026-01-22 09:00:00'),
(2,  2,  'ORD-072','2026-01-23 10:30:00','completed',   '67 Bà Triệu',             'Phạm Minh Đức',     '0911000004','duc.pham@gmail.com',      201,1483,'1B01','Hà Nội',     'Quận Hoàn Kiếm',  'Phường Hàng Bài',      'online',       37760000,'2026-01-23 10:30:00','2026-01-23 10:30:00'),
(3,  NULL,'ORD-073','2026-01-24 14:00:00','completed',   '32 Nguyễn Đình Chiểu',    'Đặng Thị Linh',     '0911000007','linh.dang@gmail.com',     202,1443,'20302','Hồ Chí Minh','Quận 3',          'Phường 5',             'bank_transfer',9200000,'2026-01-24 14:00:00','2026-01-24 14:00:00'),
(4,  1,  'ORD-074','2026-01-25 09:15:00','completed',   '55 Nguyễn Văn Cừ',        'Đinh Thị Thanh Tâm','0911000011','tam.dinh@gmail.com',      202,1444,'20401','Hồ Chí Minh','Quận 4',          'Phường 1',             'cash',         23000000,'2026-01-25 09:15:00','2026-01-25 09:15:00'),
(5,  NULL,'ORD-075','2026-01-26 11:00:00','completed',   '99 Lê Lợi',               'Mai Thị Xuân',      '0911000015','xuan.mai@gmail.com',      203,1521,'3015','Đà Nẵng',    'Quận Thanh Khê',  'Phường An Khê',        'online',       18880000,'2026-01-26 11:00:00','2026-01-26 11:00:00'),
(1,  2,  'ORD-076','2026-01-27 13:30:00','completed',   '66 Cao Thắng',            'Trần Thị Bảo Châu', '0911000019','bauchau.tran@gmail.com',  202,1443,'20307','Hồ Chí Minh','Quận 3',          'Phường 12',            'bank_transfer',37760000,'2026-01-27 13:30:00','2026-01-27 13:30:00'),
(2,  NULL,'ORD-077','2026-01-28 10:00:00','completed',   '15 Nguyễn Văn Bảo',       'Đỗ Thị Hương Giang','0911000023','giang.do@gmail.com',      202,1450,'20701','Hồ Chí Minh','Quận Gò Vấp',     'Phường 1',             'cash',         4130000,'2026-01-28 10:00:00','2026-01-28 10:00:00'),
(3,  1,  'ORD-078','2026-01-29 14:45:00','completed',   '5 Đặng Văn Bi',           'Ngô Thị Diễm My',   '0911000028','diemmy.ngo@gmail.com',    202,1453,'20601','Hồ Chí Minh','Thủ Đức',         'Phường Bình Thọ',      'online',       23000000,'2026-01-29 14:45:00','2026-01-29 14:45:00'),
(4,  NULL,'ORD-079','2026-01-30 09:30:00','completed',   '7 Ông Ích Khiêm',         'Phan Thị Hoa',      '0911000032','hoa.phan@gmail.com',      203,1520,'3008','Đà Nẵng',    'Quận Hải Châu',   'Phường Hòa Thuận Tây', 'bank_transfer',6900000,'2026-01-30 09:30:00','2026-01-30 09:30:00'),
(5,  2,  'ORD-080','2026-01-31 11:00:00','completed',   '55 Tây Sơn',              'Đặng Văn Nghĩa',    '0911000036','nghia.dang@gmail.com',    201,1486,'1C06','Hà Nội',     'Quận Đống Đa',    'Phường Quang Trung',   'cash',         42000000,'2026-01-31 11:00:00','2026-01-31 11:00:00'),
(1,  NULL,'ORD-081','2026-02-03 09:00:00','completed',   '3 Bến Chương Dương',       'Nguyễn Thị Cẩm Vân','0911000039','camvan.nguyen@gmail.com', 202,1442,'20101','Hồ Chí Minh','Quận 1',          'Phường Cầu Ông Lãnh',  'online',       7500000,'2026-02-03 09:00:00','2026-02-03 09:00:00'),
(2,  2,  'ORD-082','2026-02-04 13:00:00','completed',   '18 Phan Đình Giót',        'Phạm Thị Yến',      '0911000042','yen.pham@gmail.com',      203,1521,'3016','Đà Nẵng',    'Quận Thanh Khê',  'Phường Xuân Hà',       'bank_transfer',33930000,'2026-02-04 13:00:00','2026-02-04 13:00:00'),
(3,  NULL,'ORD-083','2026-02-06 10:00:00','completed',   '30 Đinh Tiên Hoàng',       'Bùi Quang Hào',     '0911000046','hao.bui@gmail.com',       202,1442,'20104','Hồ Chí Minh','Quận 1',          'Phường Đa Kao',        'cash',         1040000,'2026-02-06 10:00:00','2026-02-06 10:00:00'),
(4,  1,  'ORD-084','2026-02-07 14:30:00','completed',   '43 Hoàng Diệu',           'Mai Thị Ánh Tuyết', '0911000050','anhtuyet.mai@gmail.com',  202,1444,'20406','Hồ Chí Minh','Quận 4',          'Phường 13',            'online',       20520000,'2026-02-07 14:30:00','2026-02-07 14:30:00'),
(5,  2,  'ORD-085','2026-02-08 09:00:00','completed',   '14 Đinh Lễ',              'Lê Thị Cẩm Tú',     '0911000003','camtu.le@gmail.com',      201,1482,'1A03','Hà Nội',     'Quận Ba Đình',    'Phường Trúc Bạch',     'bank_transfer',37760000,'2026-02-08 09:00:00','2026-02-08 09:00:00'),
(1,  NULL,'ORD-086','2026-02-09 11:30:00','completed',   '120 Trần Hưng Đạo',        'Vũ Quang Hưng',     '0911000006','hung.vu@gmail.com',       202,1446,'20310','Hồ Chí Minh','Quận 5',          'Phường 7',             'cash',         21280000,'2026-02-09 11:30:00','2026-02-09 11:30:00'),
(2,  2,  'ORD-087','2026-02-11 10:00:00','completed',   '77 Phạm Ngũ Lão',          'Ngô Thị Mỹ Duyên',  '0911000009','duyen.ngo@gmail.com',     202,1442,'20112','Hồ Chí Minh','Quận 1',          'Phường Phạm Ngũ Lão',  'online',       42000000,'2026-02-11 10:00:00','2026-02-11 10:00:00'),
(3,  NULL,'ORD-088','2026-02-12 13:00:00','completed',   '6 Hoàng Văn Thụ',          'Phan Thị Ngọc',     '0911000013','ngoc.phan@gmail.com',     203,1520,'3010','Đà Nẵng',    'Quận Hải Châu',   'Phường Thạch Thang',   'bank_transfer',5400000,'2026-02-12 13:00:00','2026-02-12 13:00:00'),
(4,  1,  'ORD-089','2026-02-13 09:30:00','completed',   '3 Xuân Thủy',              'Võ Thị Kim Anh',    '0911000017','kimanh.vo@gmail.com',     201,1488,'1D01','Hà Nội',     'Quận Cầu Giấy',   'Phường Dịch Vọng',     'cash',         20520000,'2026-02-13 09:30:00','2026-02-13 09:30:00'),
(5,  NULL,'ORD-090','2026-02-16 11:00:00','completed',   '38 Lạc Long Quân',         'Phạm Thị Thu Hà',   '0911000021','thuha.pham@gmail.com',    202,1448,'20501','Hồ Chí Minh','Quận 11',         'Phường 1',             'online',       10620000,'2026-02-16 11:00:00','2026-02-16 11:00:00'),
(1,  2,  'ORD-091','2026-02-17 14:00:00','completed',   '45 Phổ Quang',             'Trần Minh Quân',    '0911000025','quan.tran@gmail.com',     202,1454,'20801','Hồ Chí Minh','Quận Tân Bình',   'Phường 2',             'bank_transfer',32130000,'2026-02-17 14:00:00','2026-02-17 14:00:00'),
(2,  NULL,'ORD-092','2026-02-18 10:00:00','completed',   '99 Trần Não',              'Bùi Thị Lan Anh',   '0911000029','lananh.bui@gmail.com',    202,1444,'20401','Hồ Chí Minh','Quận Bình Thạnh', 'Phường 25',            'cash',         18880000,'2026-02-18 10:00:00','2026-02-18 10:00:00'),
(3,  1,  'ORD-093','2026-02-19 13:30:00','completed',   '88 Giải Phóng',            'Mai Văn Tùng',      '0911000034','tung.mai@gmail.com',      201,1487,'1C09','Hà Nội',     'Quận Hai Bà Trưng','Phường Đồng Tâm',      'online',       26880000,'2026-02-19 13:30:00','2026-02-19 13:30:00'),
(4,  NULL,'ORD-094','2026-02-21 09:00:00','completed',   '12 Bà Huyện Thanh Quan',   'Trịnh Thị Thu',     '0911000037','thu.trinh@gmail.com',     202,1443,'20303','Hồ Chí Minh','Quận 3',          'Phường 7',             'bank_transfer',3750000,'2026-02-21 09:00:00','2026-02-21 09:00:00'),
(5,  2,  'ORD-095','2026-02-22 11:30:00','completed',   '72 Nguyễn Thị Nhỏ',        'Trần Đức Khải',     '0911000040','khai.tran@gmail.com',     202,1448,'20506','Hồ Chí Minh','Quận 11',         'Phường 10',            'cash',         37760000,'2026-02-22 11:30:00','2026-02-22 11:30:00'),
(1,  NULL,'ORD-096','2026-02-23 14:00:00','completed',   '41 Lê Hồng Phong',         'Vũ Thị Diệu Linh',  '0911000044','dieulinh.vu@gmail.com',   201,1482,'1A07','Hà Nội',     'Quận Ba Đình',    'Phường Ngọc Hà',       'online',       24640000,'2026-02-23 14:00:00','2026-02-23 14:00:00'),
(2,  2,  'ORD-097','2026-02-24 10:00:00','completed',   '99 Thống Nhất',             'Đinh Thị Thu Thủy', '0911000047','thuthuy.dinh@gmail.com',  202,1450,'20704','Hồ Chí Minh','Quận Gò Vấp',     'Phường 11',            'bank_transfer',33930000,'2026-02-24 10:00:00','2026-02-24 10:00:00'),
(3,  NULL,'ORD-098','2026-02-26 09:00:00','completed',   '88 Lê Duẩn',               'Trần Văn Bình',     '0911000002','binh.tran@gmail.com',     202,1442,'20108','Hồ Chí Minh','Quận 1',          'Phường Bến Thành',     'cash',         1440000,'2026-02-26 09:00:00','2026-02-26 09:00:00'),
(4,  1,  'ORD-099','2026-02-27 13:30:00','completed',   '101 Võ Văn Tần',            'Bùi Văn Long',      '0911000008','long.bui@gmail.com',      202,1443,'20306','Hồ Chí Minh','Quận 3',          'Phường 9',             'online',       19550000,'2026-02-27 13:30:00','2026-02-27 13:30:00'),
(5,  NULL,'ORD-100','2026-02-28 10:00:00','completed',   '44 Hải Phòng',              'Cao Minh Tuấn',     '0911000014','tuan.cao@gmail.com',      203,1520,'3012','Đà Nẵng',    'Quận Hải Châu',   'Phường Thạch Thang',   'bank_transfer',6000000,'2026-02-28 10:00:00','2026-02-28 10:00:00'),
-- Đơn tháng 3 - mix trạng thái
(1,  2,  'ORD-101','2026-03-02 09:00:00','shipping',    '5 Pasteur',                'Hoàng Thị Hồng',    '0911000005','hong.hoang@gmail.com',    202,1443,'20301','Hồ Chí Minh','Quận 3',          'Phường 6',             'online',       42000000,'2026-03-02 09:00:00','2026-03-02 09:00:00'),
(2,  NULL,'ORD-102','2026-03-03 11:00:00','shipping',   '50 Nguyễn Chí Thanh',      'Nguyễn Hoàng Khôi', '0911000018','khoi.nguyen@gmail.com',   201,1486,'1C03','Hà Nội',     'Quận Đống Đa',    'Phường Ngọc Khánh',    'cash',         20880000,'2026-03-03 11:00:00','2026-03-03 11:00:00'),
(3,  2,  'ORD-103','2026-03-04 14:00:00','shipping',    '110 Cộng Hòa',              'Lê Thị Phương Thảo','0911000026','thaophuong.le@gmail.com', 202,1454,'20806','Hồ Chí Minh','Quận Tân Bình',   'Phường 12',            'online',       37760000,'2026-03-04 14:00:00','2026-03-04 14:00:00'),
(4,  NULL,'ORD-104','2026-03-06 10:00:00','confirmed',  '16 Lý Thái Tổ',            'Hà Thị Huyền',      '0911000035','huyen.ha@gmail.com',      201,1483,'1B07','Hà Nội',     'Quận Hoàn Kiếm',  'Phường Lý Thái Tổ',    'bank_transfer',26880000,'2026-03-06 10:00:00','2026-03-06 10:00:00'),
(5,  2,  'ORD-105','2026-03-07 13:30:00','confirmed',   '90 Quang Trung',            'Lê Văn Cường',      '0911000041','cuong.le@gmail.com',      202,1450,'20703','Hồ Chí Minh','Quận Gò Vấp',     'Phường 10',            'cash',         33930000,'2026-03-07 13:30:00','2026-03-07 13:30:00'),
(1,  NULL,'ORD-106','2026-03-09 09:00:00','confirmed',  '5 Nguyễn Gia Thiều',       'Phan Văn Đạt',      '0911000048','dat.phan@gmail.com',      203,1520,'3007','Đà Nẵng',    'Quận Hải Châu',   'Phường Nam Dương',     'online',       18880000,'2026-03-09 09:00:00','2026-03-09 09:00:00'),
(2,  1,  'ORD-107','2026-03-11 11:00:00','new',         '78 Kha Vạn Cân',           'Hoàng Văn Kiên',    '0911000022','kien.hoang@gmail.com',    202,1453,'20601','Hồ Chí Minh','Thủ Đức',         'Phường Linh Đông',     'bank_transfer',24640000,'2026-03-11 11:00:00','2026-03-11 11:00:00'),
(3,  NULL,'ORD-108','2026-03-13 14:00:00','new',        '21 Hùng Vương',             'Đinh Văn Lâm',      '0911000031','lam.dinh@gmail.com',      203,1522,'3020','Đà Nẵng',    'Quận Liên Chiểu', 'Phường Hòa Khánh Bắc', 'cash',         7500000,'2026-03-13 14:00:00','2026-03-13 14:00:00'),
(4,  2,  'ORD-109','2026-03-16 10:00:00','new',         '5 Chu Văn An',             'Ngô Văn Toàn',      '0911000045','toan.ngo@gmail.com',      201,1483,'1B02','Hà Nội',     'Quận Hoàn Kiếm',  'Phường Trần Hưng Đạo', 'online',       42000000,'2026-03-16 10:00:00','2026-03-16 10:00:00'),
(5,  NULL,'ORD-110','2026-03-17 09:30:00','new',        '33 Huỳnh Văn Bánh',         'Phạm Văn Hải',      '0911000027','hai.pham@gmail.com',      202,1454,'20810','Hồ Chí Minh','Quận Phú Nhuận',  'Phường 17',            'bank_transfer',5750000,'2026-03-17 09:30:00','2026-03-17 09:30:00');
 
-- ============================================================
-- ORDER_DETAILS cho ORD-071 → ORD-110
-- order_id lần lượt 71..110, product_id 1..30 hợp lệ
-- ============================================================
INSERT INTO order_details (order_id, product_id, quantity, unit_price, discount_applied) VALUES
-- ORD-071 (id=1)
(1,  7,  1, 26880000, 0),
-- ORD-072 (id=2)
(2,  10, 1, 37760000, 3776000),
-- ORD-073 (id=3)
(3,  3,  1, 9200000,  0),
-- ORD-074 (id=4)
(4,  1,  1, 23000000, 2300000),
-- ORD-075 (id=5)
(5,  8,  1, 18880000, 0),
-- ORD-076 (id=6)
(6,  10, 1, 37760000, 3776000),
-- ORD-077 (id=7)
(7,  23, 1, 4130000,  0),
-- ORD-078 (id=8)
(8,  1,  1, 23000000, 2300000),
-- ORD-079 (id=9)
(9,  20, 1, 6900000,  0),
-- ORD-080 (id=10)
(10, 13, 1, 42000000, 4200000),
-- ORD-081 (id=11)
(11, 4,  1, 7500000,  0),
-- ORD-082 (id=12)
(12, 11, 1, 33930000, 3393000),
-- ORD-083 (id=13)
(13, 27, 1, 1040000,  0),
-- ORD-084 (id=14)
(14, 9,  1, 20520000, 2052000),
-- ORD-085 (id=15)
(15, 10, 1, 37760000, 3776000),
-- ORD-086 (id=16)
(16, 16, 1, 21280000, 0),
-- ORD-087 (id=17)
(17, 13, 1, 42000000, 4200000),
-- ORD-088 (id=18)
(18, 21, 1, 5400000,  0),
-- ORD-089 (id=19)
(19, 9,  1, 20520000, 2052000),
-- ORD-090 (id=20)
(20, 17, 1, 10620000, 0),
-- ORD-091 (id=21)
(21, 14, 1, 32130000, 3393000),
-- ORD-092 (id=22)
(22, 8,  1, 18880000, 0),
-- ORD-093 (id=23)
(23, 7,  1, 26880000, 2688000),
-- ORD-094 (id=24)
(24, 24, 1, 3750000,  0),
-- ORD-095 (id=25)
(25, 10, 1, 37760000, 3776000),
-- ORD-096 (id=26)
(26, 5,  1, 24640000, 0),
-- ORD-097 (id=27)
(27, 11, 1, 33930000, 3393000),
-- ORD-098 (id=28)
(28, 26, 1, 1440000,  0),
-- ORD-099 (id=29)
(29, 6,  1, 19550000, 1955000),
-- ORD-100 (id=30)
(30, 26, 1, 6000000,  0),
-- ORD-101 (id=31) - 2 items
(31, 13, 1, 42000000, 4200000),
-- ORD-102 (id=32)
(32, 12, 1, 20880000, 0),
-- ORD-103 (id=33)
(33, 10, 1, 37760000, 3776000),
-- ORD-104 (id=34)
(34, 7,  1, 26880000, 0),
-- ORD-105 (id=35)
(35, 11, 1, 33930000, 3393000),
-- ORD-106 (id=36)
(36, 8,  1, 18880000, 0),
-- ORD-107 (id=37)
(37, 5,  1, 24640000, 2464000),
-- ORD-108 (id=38)
(38, 4,  1, 7500000,  0),
-- ORD-109 (id=39)
(39, 13, 1, 42000000, 4200000),
-- ORD-110 (id=40)
(40, 22, 1, 5750000,  0);
 
-- ============================================================
-- IMPORT_NOTES: thêm id 26..50 (25 phiếu)
-- Gốc có 7, Additional thêm 18 (id 8..25), giờ thêm id 26..50
-- Dùng explicit id vì DataMock gốc dùng explicit id
-- ============================================================
INSERT INTO import_notes (id, admin_id, supplier_id, import_date, status, total_cost, paid_amount, created_at, updated_at) VALUES
(26, 1, 1, '2025-07-05 08:30:00', 'completed', 220000000.00, 220000000.00, '2025-07-05 08:30:00', '2025-07-05 09:30:00'),
(27, 2, 2, '2025-07-10 09:00:00', 'completed', 185000000.00, 185000000.00, '2025-07-10 09:00:00', '2025-07-10 10:00:00'),
(28, 3, 3, '2025-07-15 14:00:00', 'completed', 310000000.00, 310000000.00, '2025-07-15 14:00:00', '2025-07-15 15:00:00'),
(29, 4, 4, '2025-07-20 09:30:00', 'completed', 255000000.00, 255000000.00, '2025-07-20 09:30:00', '2025-07-20 10:30:00'),
(30, 5, 5, '2025-07-25 10:00:00', 'completed', 170000000.00, 170000000.00, '2025-07-25 10:00:00', '2025-07-25 11:00:00'),
(31, 1, 6, '2025-08-01 08:00:00', 'completed', 395000000.00, 395000000.00, '2025-08-01 08:00:00', '2025-08-01 09:30:00'),
(32, 2, 1, '2025-08-05 13:00:00', 'completed', 145000000.00, 145000000.00, '2025-08-05 13:00:00', '2025-08-05 14:00:00'),
(33, 3, 2, '2025-08-10 09:00:00', 'completed', 275000000.00, 200000000.00, '2025-08-10 09:00:00', '2025-08-10 10:00:00'),
(34, 4, 3, '2025-08-15 14:30:00', 'completed', 330000000.00, 330000000.00, '2025-08-15 14:30:00', '2025-08-15 15:30:00'),
(35, 5, 4, '2025-08-20 09:00:00', 'completed', 200000000.00, 200000000.00, '2025-08-20 09:00:00', '2025-08-20 10:00:00'),
(36, 1, 5, '2025-08-25 10:30:00', 'completed', 460000000.00, 460000000.00, '2025-08-25 10:30:00', '2025-08-25 12:00:00'),
(37, 2, 6, '2025-09-01 08:00:00', 'completed', 155000000.00, 155000000.00, '2025-09-01 08:00:00', '2025-09-01 09:00:00'),
(38, 3, 1, '2025-09-05 13:30:00', 'completed', 290000000.00, 290000000.00, '2025-09-05 13:30:00', '2025-09-05 14:30:00'),
(39, 4, 2, '2025-09-08 09:00:00', 'completed', 215000000.00, 150000000.00, '2025-09-08 09:00:00', '2025-09-08 10:00:00'),
(40, 5, 3, '2025-09-12 14:00:00', 'completed', 375000000.00, 375000000.00, '2025-09-12 14:00:00', '2025-09-12 15:30:00'),
(41, 1, 4, '2026-03-18 08:00:00', 'pending',   165000000.00,   0.00,        '2026-03-18 08:00:00', '2026-03-18 08:00:00'),
(42, 2, 5, '2026-03-19 09:00:00', 'pending',   285000000.00,   0.00,        '2026-03-19 09:00:00', '2026-03-19 09:00:00'),
(43, 3, 6, '2026-03-20 10:00:00', 'pending',   195000000.00,   0.00,        '2026-03-20 10:00:00', '2026-03-20 10:00:00'),
(44, 4, 1, '2025-10-15 08:30:00', 'completed', 340000000.00, 340000000.00, '2025-10-15 08:30:00', '2025-10-15 09:30:00'),
(45, 5, 2, '2025-10-22 13:00:00', 'completed', 180000000.00, 180000000.00, '2025-10-22 13:00:00', '2025-10-22 14:00:00'),
(46, 1, 3, '2025-11-05 09:00:00', 'completed', 405000000.00, 405000000.00, '2025-11-05 09:00:00', '2025-11-05 10:30:00'),
(47, 2, 4, '2025-11-15 14:00:00', 'completed', 225000000.00, 225000000.00, '2025-11-15 14:00:00', '2025-11-15 15:00:00'),
(48, 3, 5, '2025-12-05 09:30:00', 'completed', 320000000.00, 320000000.00, '2025-12-05 09:30:00', '2025-12-05 10:30:00'),
(49, 4, 6, '2026-01-10 08:00:00', 'completed', 270000000.00, 270000000.00, '2026-01-10 08:00:00', '2026-01-10 09:00:00'),
(50, 5, 1, '2026-02-10 10:00:00', 'completed', 350000000.00, 350000000.00, '2026-02-10 10:00:00', '2026-02-10 11:30:00');
 
-- ============================================================
-- IMPORT_NOTE_DETAILS cho import_notes id 26..50
-- Không dùng explicit id (AUTO_INCREMENT tiếp theo)
-- ============================================================
INSERT INTO import_note_details (import_note_id, product_id, quantity, import_price) VALUES
-- note 26
(26, 1,  8, 20000000.00),
(26, 5,  5, 22000000.00),
-- note 27
(27, 4, 15,  6000000.00),
(27, 24,12,  3000000.00),
-- note 28
(28, 6, 10, 17000000.00),
(28, 7,  8, 24000000.00),
-- note 29
(29, 2,  5, 30000000.00),
(29, 11, 5, 29000000.00),
-- note 30
(30, 3, 12,  8000000.00),
(30, 22,10,  5000000.00),
-- note 31
(31, 10,10, 32000000.00),
(31, 13, 5, 35000000.00),
-- note 32
(32, 17,10,  9000000.00),
(32, 23,15,  3500000.00),
-- note 33
(33, 8, 10, 18000000.00),
(33, 12, 5, 16000000.00),
-- note 34
(34, 9,  8, 18000000.00),
(34, 18, 5, 22000000.00),
-- note 35
(35, 15,10, 14000000.00),
(35, 20,10,  6000000.00),
-- note 36
(36, 5,  8, 22000000.00),
(36, 7, 10, 24000000.00),
(36, 10, 5, 32000000.00),
-- note 37
(37, 25,15,  1800000.00),
(37, 28,20,   600000.00),
-- note 38
(38, 6, 10, 17000000.00),
(38, 14, 8, 27000000.00),
-- note 39
(39, 16, 8, 19000000.00),
(39, 29, 8,  8000000.00),
-- note 40
(40, 11,10, 29000000.00),
(40, 13, 5, 35000000.00),
-- note 41
(41, 1, 10, 20000000.00),
(41, 7,  5, 24000000.00),
-- note 42
(42, 10, 5, 32000000.00),
(42, 5,  5, 22000000.00),
(42, 8, 10, 18000000.00),
-- note 43
(43, 6, 10, 17000000.00),
(43, 9,  5, 18000000.00),
-- note 44
(44, 2,  5, 30000000.00),
(44, 13, 5, 35000000.00),
-- note 45
(45, 4, 15,  6000000.00),
(45, 21,10,  4500000.00),
-- note 46
(46, 11,10, 29000000.00),
(46, 12, 5, 16000000.00),
(46, 14, 5, 27000000.00),
-- note 47
(47, 15,10, 14000000.00),
(47, 16, 5, 19000000.00),
-- note 48
(48, 8, 10, 18000000.00),
(48, 9,  8, 18000000.00),
-- note 49
(49, 1, 10, 20000000.00),
(49, 6,  5, 17000000.00),
-- note 50
(50, 10, 8, 32000000.00),
(50, 11, 5, 29000000.00),
(50, 13, 3, 35000000.00);
 
-- ============================================================
-- IMPORT_NOTE_PAYMENTS cho import_notes id 26..50
-- Chỉ các phiếu completed và có paid_amount > 0
-- Không dùng explicit id (AUTO_INCREMENT tiếp theo)
-- ============================================================
INSERT INTO import_note_payments (import_note_id, admin_id, amount, created_at, updated_at) VALUES
(26, 1, 220000000.00, '2025-07-05 09:30:00', '2025-07-05 09:30:00'),
(27, 2, 185000000.00, '2025-07-10 10:00:00', '2025-07-10 10:00:00'),
(28, 3, 310000000.00, '2025-07-15 15:00:00', '2025-07-15 15:00:00'),
(29, 4, 255000000.00, '2025-07-20 10:30:00', '2025-07-20 10:30:00'),
(30, 5, 170000000.00, '2025-07-25 11:00:00', '2025-07-25 11:00:00'),
(31, 1, 395000000.00, '2025-08-01 09:30:00', '2025-08-01 09:30:00'),
(32, 2, 145000000.00, '2025-08-05 14:00:00', '2025-08-05 14:00:00'),
-- note 33: thanh toán 2 lần (paid=200tr / total=275tr)
(33, 3, 150000000.00, '2025-08-10 10:00:00', '2025-08-10 10:00:00'),
(33, 3,  50000000.00, '2025-08-22 09:00:00', '2025-08-22 09:00:00'),
(34, 4, 330000000.00, '2025-08-15 15:30:00', '2025-08-15 15:30:00'),
(35, 5, 200000000.00, '2025-08-20 10:00:00', '2025-08-20 10:00:00'),
(36, 1, 460000000.00, '2025-08-25 12:00:00', '2025-08-25 12:00:00'),
(37, 2, 155000000.00, '2025-09-01 09:00:00', '2025-09-01 09:00:00'),
(38, 3, 290000000.00, '2025-09-05 14:30:00', '2025-09-05 14:30:00'),
-- note 39: thanh toán 2 lần (paid=150tr / total=215tr)
(39, 4, 100000000.00, '2025-09-08 10:00:00', '2025-09-08 10:00:00'),
(39, 4,  50000000.00, '2025-09-20 09:00:00', '2025-09-20 09:00:00'),
(40, 5, 375000000.00, '2025-09-12 15:30:00', '2025-09-12 15:30:00'),
(44, 4, 340000000.00, '2025-10-15 09:30:00', '2025-10-15 09:30:00'),
(45, 5, 180000000.00, '2025-10-22 14:00:00', '2025-10-22 14:00:00'),
(46, 1, 405000000.00, '2025-11-05 10:30:00', '2025-11-05 10:30:00'),
(47, 2, 225000000.00, '2025-11-15 15:00:00', '2025-11-15 15:00:00'),
(48, 3, 320000000.00, '2025-12-05 10:30:00', '2025-12-05 10:30:00'),
(49, 4, 270000000.00, '2026-01-10 09:00:00', '2026-01-10 09:00:00'),
(50, 5, 350000000.00, '2026-02-10 11:30:00', '2026-02-10 11:30:00');
 