<?php

namespace Aenzenith\LaravelAchiever\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchievementProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'achievement_id',
        'user_id',
        'progress',
        'unlocked_at',
        'notified_at',
    ];

    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }
}
