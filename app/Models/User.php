<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',  // مش بنرجع الباسورد في الـ API response أبداً
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ===========================
    // JWT Methods (مطلوبة لمكتبة JWT)
    // ===========================
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,  // بنحط الـ role في الـ token
        ];
    }

    // ===========================
    // Relationships (العلاقات)
    // ===========================

    // المستخدم ينتمي لشركة واحدة
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // المستخدم عنده كتير سجلات وقت
    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }

    // ===========================
    // Helper Methods
    // ===========================
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
