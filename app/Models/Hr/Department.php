<?php

declare(strict_types=1);

namespace App\Models\Hr;

use Database\Factories\Hr\DepartmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'parent', 'head', 'status'])]
final class Department extends Model
{
    protected $table = 'hr_departments';

    /** @use HasFactory<DepartmentFactory> */
    use HasFactory;
}
