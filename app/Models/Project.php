<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'status',
        'deadline',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    // المشروع ينتمي لشركة
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // المشروع عنده كتير سجلات وقت
    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }

    // حساب إجمالي ساعات المشروع
    public function getTotalHoursAttribute(): float
    {
        return round($this->timeLogs()->sum('duration_minutes') / 60, 2);
    }
}
