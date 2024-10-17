<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'exception',
        'url',
        'method',
        'status_code',
        'stack_trace',
        'error_by'
    ];

    // Casts for JSON fields
    protected $casts = [
        'user_data' => 'array',
    ];
}
