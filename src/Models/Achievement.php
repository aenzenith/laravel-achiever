<?php

namespace Aenzenith\LaravelAchiever\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'achievement_set_id',
        'name',
        'description',
        'icon_path',
        'operation_key',
        'model_id',
        'points',
    ];

    public function set()
    {
        return $this->belongsTo(AchievementSet::class);
    }
}
