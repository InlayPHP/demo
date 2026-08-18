<?php

declare(strict_types=1);

namespace App\Models\Hr;

use Database\Factories\Hr\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'department', 'employment_type', 'status', 'hire_date', 'salary', 'skills', 'metadata'])]
final class Employee extends Model
{
    protected $table = 'hr_employees';

    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'salary' => 'decimal:2',
            'skills' => 'array',
            'metadata' => 'array',
        ];
    }
}
