<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MindEvent extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'date', 'type', 'label', 'note', 'icon'];

    protected function casts(): array
    {
        return [
            // 'datetime:Y-m-d' garante uma instância de Carbon imutável
            // com formato de data consistente (sem hora/timezone),
            // permitindo chamar ->format('Y-m-d') com segurança.
            'date' => 'datetime:Y-m-d',
        ];
    }

    /**
     * Eventos do usuário dono deste evento.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}