<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'subject', 'body', 'type', 'is_active', 'variables'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'variables' => 'array',
    ];
}


