<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment', 
        'user_id',
        'commentable_id', 
        'commentable_type'
    ];

    // Define the polymorphic relationship
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    // A comment belongs to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
