<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MindPerson extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nickname',
        'photo',
        'notes',
        'birth_day',
        'birth_month',
        'birth_year',
        'user_id',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }

    public function groups()
    {
        return $this->belongsToMany(
            MindGroup::class,
            'mind_group_mind_person'
        );
    }
}