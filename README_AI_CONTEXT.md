📌 1. Tổng quan hệ thống
# Project: Warehouse & Multi-channel Sales System (Backend API)

Tech stack:
- Laravel
- MySQL
- Redis
- Cloudinary
- GHN API
- Telegram Bot

Architecture:
- Layered Architecture (Controller → Service → Model)
- RESTful API
📌 2. Các module chính
## Modules

- Authentication (Admin, Customer)
- Product Management
- Category & Brand
- User Management
- Inventory Management
- Order 
📌 3. Authentication
## Authentication

- Admin:
  - Login / Logout
  - Middleware: AdminAuth

- Customer:
  - Register / Login / Logout
  - Middleware: CustomerAuth
📌 4. Product Flow (QUAN TRỌNG)
## Product Flow

- Controller → Service → Model

Features:
- CRUD product
- Upload image (Cloudinary)
- Soft delete / Hide product

Business logic:
- Product có status (active / inactive)
📌 5. Database (tóm tắt)
## Database

Tables:
- users
- products
- categories
- brands
- orders
- order_items

Relationships:
- Category 1-N Product
- Brand 1-N Product
- User 1-N Orders
📌 6. Các tích hợp ngoài (RẤT QUAN TRỌNG)
## External Integrations

- Cloudinary:
  → lưu ảnh sản phẩm

- GHN API:
  → lấy địa chỉ chuẩn Việt Nam

- Redis:
  → cache danh sách sản phẩm

- Queue:
  → xử lý bulk import tồn kho

- Task Scheduling:
  → báo cáo doanh thu hàng ngày

- Telegram Bot:
  → gửi thông báo hệ thống
📌 7. Flow quan trọng
## Important Flows

1. Create Product
2. Import Inventory (Queue)
3. Get Product List (Cache Redis)
4. Daily Report (Scheduler)