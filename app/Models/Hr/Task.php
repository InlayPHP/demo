<?php

declare(strict_types=1);

namespace App\Models\Hr;

use Database\Factories\Hr\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'project', 'assignee', 'priority', 'status', 'due_date', 'estimate'])]
final class Task extends Model
{
    protected $table = 'hr_tasks';

    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['due_date' => 'date', 'estimate' => 'decimal:2'];
    }
}
