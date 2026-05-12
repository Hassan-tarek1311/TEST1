<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'logo',
    ];

    // شركة واحدة عندها كتير موظفين
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // شركة واحدة عندها كتير مشاريع
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
