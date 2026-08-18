<?php

declare(strict_types=1);

namespace App\Models\Hr;

use Database\Factories\Hr\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'status', 'owner', 'budget', 'due_date', 'plan'])]
final class Project extends Model
{
    protected $table = 'hr_projects';

    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'due_date' => 'date',
            'plan' => 'array',
        ];
    }
}
