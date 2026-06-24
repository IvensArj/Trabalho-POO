<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MindGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
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

    public function people()
    {
        return $this->belongsToMany(
            MindPerson::class,
            'mind_group_mind_person'
        );
    }
}