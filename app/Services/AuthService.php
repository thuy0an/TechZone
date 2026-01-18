<?php 
namespace App\Services;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;

class AuthService
{
    /**
     * Xử lý đăng ký Khách hàng
     */
    public function registerUser(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone']
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Xử lý đăng nhập chung (Dùng cho cả User và Admin)
     * Tùy theo $guard truyền vào mà xử lý đăng nhập cho users hoặc admins
     * => xem Authentication Guards và Providers trong config/auth.php 
     * Mặc định là 'web'
     */
    public function login($email, $password, $guard = 'web')
    {
        // Kiểm tra thông tin dăng nhập Guard chỉ định
        if (!Auth::guard($guard)->attempt(['email' => $email, 'password' => $password])){
                throw new Exception("Email hoặc mật khẩu không chính xác");
        }

        $user = Auth::guard($guard)->user();

        if ($guard === 'web' && $user->is_locked) {
            throw new Exception('Tài khoản của bạn đã bị khóa.');
        }
        if ($guard === 'admin' && !$user->is_active) {
            throw new Exception('Tài khoản nhân viên đã bị vô hiệu hóa.');
        }

        // Tạo token
        $token = $user->createToken($guard . '_token')->plainTextToken;

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'role' => $guard === 'admin' ? $user->role : 'customer'
        ];
    }
}


?>
