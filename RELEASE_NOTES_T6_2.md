# 🛒 Cập nhật hệ thống: Giỏ hàng & Đồng bộ Local-First

Bản cập nhật này tập trung hoàn thiện module **Giỏ hàng (Cart)** phía Client, chuyển đổi sang mô hình **Hybrid Local-First** (Ưu tiên cập nhật LocalStorage trước khi đồng bộ Server).

> 💡 **Lưu ý:** Hệ thống tự động xử lý Session cho khách vãng lai, cho phép mua hàng mà không cần đăng nhập.

---

## 1. Tính năng mới

### ✨ Optimistic UI (Giao diện lạc quan)
- **Phản hồi tức thì (0ms latency):** Mọi thao tác Thêm/Sửa/Xóa sản phẩm đều được cập nhật ngay lập tức trên giao diện.
- **Thông báo trạng thái (Toast):**
  - 🟢 **Xanh:** Thành công.
  - 🔴 **Đỏ:** Lỗi (Hết hàng, Lỗi mạng...).

### 🔄 Background Sync (Đồng bộ ngầm)
- **Tự động đồng bộ:** Hệ thống tự động gửi API xuống Server sau khi cập nhật LocalStorage.
- **Cơ chế Rollback:** Tự động hoàn tác dữ liệu về trạng thái cũ nếu Server trả về lỗi (ví dụ: Hết hàng, Mất mạng).

### 👤 Guest Cart Support (Hỗ trợ khách vãng lai)
- **Session ID tự động:** Hệ thống tự động sinh `UUID` cho khách chưa đăng nhập.
- **Lưu trữ & Gộp giỏ hàng:** Giỏ hàng được lưu theo `Session ID` và sẽ tự động gộp khi User đăng nhập.

---

## 2. Cấu trúc Module

Hệ thống giỏ hàng được tổ chức thành 3 tầng rõ rệt:

| Tầng (Layer) | File Path | Nhiệm vụ chính |
| :--- | :--- | :--- |
| **Tầng UI (View)** | `layouts/client.blade.php` | Chứa các thành phần giao diện: Sidebar, Badge số lượng, Toast thông báo. |
| **Tầng Client Logic (JS)** | `public/js/client/cart.js` | Quản lý `LocalStorage`, Render HTML, xử lý Debounce và Logic UI. |
| **Tầng Core & Data (API)** | `public/js/api.js`<br>`app/Services/CartService.php` | Quản lý kết nối Server, xử lý logic kho, tính giá và đồng bộ dữ liệu. |

---

## 3. Lưu ý kỹ thuật quan trọng (Backend Dev)

⚠️ **CẢNH BÁO:** Các logic dưới đây ảnh hưởng trực tiếp đến tính toàn vẹn của dữ liệu.

- **Refresh Data:** Trong `CartService`, **bắt buộc** gọi `$cart->load('items')` sau khi `Create` hoặc `Update` để đảm bảo tổng tiền được tính toán chính xác.
- **No-Store Cache:** File `api.js` đã được cấu hình để **chặn cache trình duyệt** cho các request GET. **TUYỆT ĐỐI KHÔNG XÓA** cấu hình này.
- **Debounce:** Hàm cập nhật số lượng được thiết lập độ trễ **500ms** để tránh spam request lên server.

---

## 4. Hành động bắt buộc (Action Required)

Thành viên team sau khi pull code về **BẮT BUỘC** thực hiện:

Chạy lệnh migration để đảm bảo bảng `carts` và `cart_items` đúng cấu trúc mới:
```bash
php artisan migrate