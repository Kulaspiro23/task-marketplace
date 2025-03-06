<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'taker_id',
        'status',
        'category',
        'skills',
        'deadline',
    ];
    
    protected $casts = [
        'skills' => 'array',
        'deadline' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function taker()
    {
        return $this->belongsTo(User::class, 'taker_id');
    }
}
