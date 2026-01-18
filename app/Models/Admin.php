<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // protected $table = "admins";

    protected $fillalbe = [
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
