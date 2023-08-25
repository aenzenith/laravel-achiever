<?php

namespace Aenzenith\LaravelAchiever\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchievementSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }
}
