<?php

declare(strict_types=1);

namespace App\Models\Hr;

use Database\Factories\Hr\TimesheetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee', 'project', 'work_date', 'hours', 'status', 'notes'])]
final class Timesheet extends Model
{
    protected $table = 'hr_timesheets';

    /** @use HasFactory<TimesheetFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['work_date' => 'date', 'hours' => 'decimal:2'];
    }
}
