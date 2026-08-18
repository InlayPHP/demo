<?php

declare(strict_types=1);

namespace App\Models\Hr;

use Database\Factories\Hr\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee', 'category', 'status', 'amount', 'submitted_at', 'approved_at', 'line_items'])]
final class Expense extends Model
{
    protected $table = 'hr_expenses';

    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'line_items' => 'array',
        ];
    }
}
