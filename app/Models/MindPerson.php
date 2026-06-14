<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MindPerson extends Model
{
    use HasFactory;

    protected $fillable = [
        'mind_group_id',
        'name',
        'nickname',
        'photo',
        'notes',
    ];

    public function group()
    {
        return $this->belongsTo(MindGroup::class, 'mind_group_id');
    }
}
