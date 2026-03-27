<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportJob extends BaseModel
{
    use HasFactory;

    // Định nghĩa các hằng số trạng thái (Tránh dùng chuỗi cứng trong code)
    const STATUS_PENDING    = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED  = 'completed';
    const STATUS_COMPLETED_ERRORS = 'completed_with_errors'; 
    const STATUS_FAILED     = 'failed';

    /**
     * Các cột có thể gán giá trị hàng loạt (Mass Assignable)
     */
    protected $fillable = [
        'file_name',
        'file_path',
        'status',
        'total_rows',
        'processed_rows',
        'error_message',
        'errors',
    ];
    /**
     * Ép kiểu dữ liệu cho các cột đặc biệt
     */
    protected $casts = [
        'total_rows'     => 'integer',
        'processed_rows' => 'integer',
        'errors' => 'array',
    ];

    /**
     * Helper: Kiểm tra xem Job đã xong chưa
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Helper: Tính phần trăm tiến độ (Dùng cho Frontend hiển thị Progress Bar)
     */
    public function getProgressAttribute(): int
    {
        if ($this->total_rows <= 0) return 0;

        return (int) round(($this->processed_rows / $this->total_rows) * 100);
    }
}
