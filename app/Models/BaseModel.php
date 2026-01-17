<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * BaseModel - Base class cho tất cả Eloquent Models
 * 
 * Features:
 * - Soft delete support
 * - Auto UUID generation
 * - Searchable scope
 * - Format helpers (currency, date)
 */
abstract class BaseModel extends Model
{
    use HasFactory;

    /**
     * Các trường không được mass assignment
     */
    protected $guarded = ['id'];

    /**
     * Boot model events
     */
    protected static function boot()
    {
        parent::boot();

        // Tự động tạo UUID nếu model có trường uuid
        static::creating(function ($model) {
            if (in_array('uuid', $model->getFillable()) && empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
        });
    }

    // =========================================
    // SCOPES
    // =========================================

    /**
     * Scope để lọc theo trạng thái active
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope để lọc theo trạng thái inactive
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope để tìm kiếm theo từ khóa
     */
    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        $searchableFields = $this->getSearchableFields();

        return $query->where(function ($q) use ($search, $searchableFields) {
            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'LIKE', "%{$search}%");
            }
        });
    }

    /**
     * Lấy danh sách các trường có thể tìm kiếm
     * Override trong subclass
     */
    protected function getSearchableFields(): array
    {
        return ['name'];
    }

    // =========================================
    // HELPERS
    // =========================================

    /**
     * Format số tiền VNĐ
     */
    public function formatCurrency($amount): string
    {
        return number_format($amount, 0, ',', '.') . ' ₫';
    }

    /**
     * Format ngày tháng
     */
    public function formatDate($date, $format = 'd/m/Y'): string
    {
        return $date ? $date->format($format) : '';
    }

    /**
     * Format ngày giờ
     */
    public function formatDateTime($datetime, $format = 'd/m/Y H:i'): string
    {
        return $datetime ? $datetime->format($format) : '';
    }

    /**
     * Kiểm tra model có sử dụng soft delete không
     */
    public function usesSoftDelete(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive(static::class));
    }

    /**
     * Tạo slug từ field
     */
    public function generateSlug($field = 'name'): string
    {
        $value = $this->getAttribute($field);
        return $value ? Str::slug($value) : '';
    }
}
