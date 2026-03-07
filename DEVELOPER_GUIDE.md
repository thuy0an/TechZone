# Developer Guide: Đồng bộ Database & Test API

Tài liệu này hướng dẫn các thành viên trong nhóm cách cập nhật cấu trúc Database mới nhất và quy trình test các API nghiệp vụ (Auth, Cart, Checkout) trên môi trường Local.

## 1) Cập nhật cấu trúc Database

Sau khi `git pull` code mới về, **không cần import file SQL thủ công**. Hãy sử dụng hệ thống Migration của Laravel để đảm bảo cấu trúc bảng khớp 100% với code hiện tại. Do có thêm 1 vài bảng lưu token

> **⚠️ Lưu ý quan trọng:** Lệnh này sẽ xóa sạch dữ liệu cũ để xây dựng lại bảng mới.

Sau khi Migrate xong, sử dụng file DataMock.sql để import đầy đủ dữ liệu

## 2) Quy trình lấy Token để Test API (Authentication)
Các API thuộc nhánh storefront (Giỏ hàng, Thanh toán) đều được bảo vệ bởi Sanctum (auth:sanctum). Để test được, bạn cần có một chuỗi Bearer Token.

### Cách A: Gọi API Login (Dành cho Postman / Frontend)
Nếu trong database đã có sẵn user, hãy mở Postman và gọi API Login:

* Method: POST

* Headers: 
> accept: application/json

* URL: http://127.0.0.1:8000/api/storefront/login

* Body (tab raw > JSON):
```
{
    "email": "user@example.com",
    "password": "password"
}
```

### Hoặc có thể thử Register rồi Login bằng User vừa tạo

### Cách B: Sinh Token bằng Tinker (Test nhanh cho Backend)
* Mở Terminal và chạy công cụ Tinker:
```bash
php artisan tinker
```

* Lấy user đầu tiên và sinh token:
``` bash
$user = App\Models\User::first();
$user->createToken('TestToken')->plainTextToken;
```
* Copy chuỗi vừa được in ra màn hình (Ví dụ: 1|7etyTBJsF2...) và gõ exit để thoát

## 3) Cấu hình công cụ Test API Bảo mật
### Để không bị lỗi 401 Unauthorized khi test API, hãy làm theo các bước:

> 1.Mở tab API cần test trên Postman (VD: Thêm vào giỏ hàng).

> 2.Chuyển sang tab Authorization.

> 3.Tại mục Type, chọn Bearer Token.

> 4.Dán chuỗi Token lấy được ở Phần 2 vào ô Token.

> 5.Chuyển sang tab Headers, đảm bảo có cặp key-value: Accept = application/json.

## 4) Các Endpoints Storefront Đã Hoàn Thành
Dưới đây là danh sách các API đã sẵn sàng để Front-end tích hợp:
### Xem giỏ hàng:
> GET: /api/storefront/cart

### Thêm vào giỏ:
> POST:	/api/storefront/cart/add

### Xóa sản phẩm:
> DELETE: /api/storefront/cart/remove/{cartItemId}

#### Tab Authorization Headers: 
```
    Authorization : Bearer 
    Token: <access_token>
```
#### Ví dụ Body - Thêm vào giỏ (JSON):
```json
{
    "product_id": 1,
    "quantity": 2
}
```

## 5) 💳 Thanh toán & Đơn hàng (Checkout)
### Headers: 
```
    Authorization: Bearer <access_token>
```

### Chốt đơn hàng:
>POST: /api/storefront/checkout	

### Lịch sử đơn hàng:
>GET: /api/storefront/orders

### Ví dụ Body - Chốt đơn hàng (JSON):
### Body:
```json
{
    "receiver_name": "Nguyễn Văn A",
    "receiver_phone": "0901234567",
    "shipping_address": "123 Đường ABC, Quận 1, TP.HCM",
    "payment_method": "cash"
}
```