<?php

declare(strict_types=1);

namespace App\Validation;

use Illuminate\Validation\Rule;
use Inlay\Validation\Validation;
use Inlay\Validation\ValidationContext;

/**
 * The demo keeps all showcase rules in one application-owned class so the
 * resource examples stay easy to copy. A real application can split these by
 * bounded context without changing the Inlay resource contracts.
 */
final class ShowcaseRules extends Validation
{
    public function rules(ValidationContext $context): array
    {
        $resource = (string) $context->option('resource');

        return match (true) {
            str_ends_with($resource, '\\ProductResource') => [
                'name' => ['required', 'string', 'max:255'],
                'sku' => ['required', 'string', 'max:80', Rule::unique('shop_products', 'sku')->ignore($context->record())],
                'status' => ['required', Rule::in(['active', 'draft', 'archived'])],
                'price' => ['required', 'numeric', 'min:0'],
                'stock' => ['required', 'integer', 'min:0'],
                'description' => ['nullable', 'string', 'max:2000'],
                'featured' => ['boolean'],
            ],
            str_ends_with($resource, '\\BrandResource') => [
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('shop_brands', 'slug')->ignore($context->record())],
                'status' => ['required', Rule::in(['active', 'inactive'])],
                'website' => ['nullable', 'url', 'max:255'],
                'sort' => ['required', 'integer', 'min:0'],
            ],
            str_ends_with($resource, '\\ProductCategoryResource') => [
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('shop_product_categories', 'slug')->ignore($context->record())],
                'description' => ['nullable', 'string', 'max:2000'],
            ],
            str_ends_with($resource, '\\CustomerResource') => [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('shop_customers', 'email')->ignore($context->record())],
                'phone' => ['nullable', 'string', 'max:40'],
                'status' => ['required', Rule::in(['active', 'inactive'])],
                'notes' => ['nullable', 'string', 'max:2000'],
            ],
            str_ends_with($resource, '\\OrderResource') => [
                'number' => ['required', 'string', 'max:30', Rule::unique('shop_orders', 'number')->ignore($context->record())],
                'status' => ['required', Rule::in(['pending', 'paid', 'shipped', 'refunded', 'cancelled'])],
                'payment_method' => ['required', Rule::in(['card', 'bank_transfer', 'cash'])],
                'total' => ['required', 'numeric', 'min:0'],
                'placed_at' => ['required', 'date'],
                'notes' => ['nullable', 'string', 'max:2000'],
                'items' => ['nullable', 'array'],
                'items.*.name' => ['required', 'string', 'max:255'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'items.*.price' => ['required', 'numeric', 'min:0'],
            ],
            str_ends_with($resource, '\\AuthorResource') => [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('blog_authors', 'email')->ignore($context->record())],
                'bio' => ['nullable', 'string', 'max:2000'],
                'active' => ['boolean'],
            ],
            str_ends_with($resource, '\\CategoryResource') => [
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('blog_categories', 'slug')->ignore($context->record())],
                'description' => ['nullable', 'string', 'max:2000'],
            ],
            str_ends_with($resource, '\\EmployeeResource') => [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('hr_employees', 'email')->ignore($context->record())],
                'department' => ['required', 'string', 'max:80'],
                'employment_type' => ['required', Rule::in(['full-time', 'part-time', 'contract'])],
                'status' => ['required', Rule::in(['active', 'on-leave', 'inactive'])],
                'hire_date' => ['required', 'date'],
                'salary' => ['nullable', 'numeric', 'min:0'],
                'skills' => ['nullable', 'array'],
                'skills.*' => ['string'],
                'metadata' => ['nullable', 'array'],
            ],
            str_ends_with($resource, '\\DepartmentResource') => [
                'name' => ['required', 'string', 'max:255'],
                'parent' => ['nullable', 'string', 'max:255'],
                'head' => ['nullable', 'string', 'max:255'],
                'status' => ['required', Rule::in(['active', 'archived'])],
            ],
            str_ends_with($resource, '\\TaskResource') => [
                'title' => ['required', 'string', 'max:255'],
                'project' => ['nullable', 'string', 'max:255'],
                'assignee' => ['nullable', 'string', 'max:255'],
                'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
                'status' => ['required', Rule::in(['todo', 'in-progress', 'blocked', 'done'])],
                'due_date' => ['nullable', 'date'],
                'estimate' => ['nullable', 'numeric', 'min:0'],
            ],
            str_ends_with($resource, '\\TimesheetResource') => [
                'employee' => ['required', 'string', 'max:255'],
                'project' => ['nullable', 'string', 'max:255'],
                'work_date' => ['required', 'date'],
                'hours' => ['required', 'numeric', 'min:0'],
                'status' => ['required', Rule::in(['draft', 'submitted', 'approved'])],
                'notes' => ['nullable', 'string', 'max:2000'],
            ],
            str_ends_with($resource, '\\ProjectResource') => [
                'name' => ['required', 'string', 'max:255'],
                'status' => ['required', Rule::in(['planned', 'in-progress', 'at-risk', 'completed'])],
                'owner' => ['required', 'string', 'max:255'],
                'budget' => ['required', 'numeric', 'min:0'],
                'due_date' => ['nullable', 'date'],
                'plan' => ['nullable', 'array'],
            ],
            str_ends_with($resource, '\\LeaveRequestResource') => [
                'employee' => ['required', 'string', 'max:255'],
                'type' => ['required', Rule::in(['annual', 'sick', 'parental', 'unpaid'])],
                'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                'notes' => ['nullable', 'string', 'max:2000'],
            ],
            str_ends_with($resource, '\\ExpenseResource') => [
                'employee' => ['required', 'string', 'max:255'],
                'category' => ['required', Rule::in(['Travel', 'Equipment', 'Meals', 'Training', 'Software'])],
                'status' => ['required', Rule::in(['submitted', 'approved', 'rejected', 'reimbursed'])],
                'amount' => ['required', 'numeric', 'min:0'],
                'submitted_at' => ['required', 'date'],
                'approved_at' => ['nullable', 'date'],
                'line_items' => ['nullable', 'array'],
            ],
            default => [],
        };
    }

    public function prepare(array $data, ValidationContext $context): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
        }

        return $data;
    }
}
