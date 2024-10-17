<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDependency extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id', 
        'depends_on_task_id'
    ];

    // The task that is blocked by a dependency
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    // The task that this task depends on
    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'depends_on_task_id');
    }
}
