<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class TimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    // ===========================
    // Relationships
    // ===========================
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ===========================
    // Business Logic
    // ===========================

    // لما الموظف يوقف الساعة، بنحسب المدة تلقائياً
    public function stop(): void
    {
        $this->ended_at = Carbon::now();
        $this->duration_minutes = Carbon::parse($this->started_at)
            ->diffInMinutes($this->ended_at);
        $this->save();
    }

    // بنرجع المدة بصيغة مقروءة: "2h 30m"
    public function getDurationFormattedAttribute(): string
    {
        if (!$this->duration_minutes) {
            return 'In progress...';
        }
        $hours   = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;
        return "{$hours}h {$minutes}m";
    }

    // هل السجل ده لسه شغّال؟
    public function isActive(): bool
    {
        return is_null($this->ended_at);
    }
}
