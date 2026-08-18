<?php

declare(strict_types=1);

namespace App\Models\Hr;

use Database\Factories\Hr\LeaveRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee', 'type', 'status', 'start_date', 'end_date', 'notes'])]
final class LeaveRequest extends Model
{
    protected $table = 'hr_leave_requests';

    /** @use HasFactory<LeaveRequestFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}
