<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // protected $table = "admins";

    protected $fillable = [
        'name', 
        'email',
        'password',
        'phone',
        'role',     // 'super_admin', 'warehouse_staff', 'sales_staff'
        'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Helper Methods
     */

    public function isSuperAdmin() : bool {
        return $this->role === 'super_admin';
    }

    public function isWarehouseStaff() : bool {
        return $this->role === 'warehouse_staff';
    }
}
