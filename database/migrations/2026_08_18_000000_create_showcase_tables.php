<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_product_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('shop_brands', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status', 20)->default('active')->index();
            $table->string('website')->nullable();
            $table->unsignedInteger('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('shop_products', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('sku', 80)->unique();
            $table->string('status', 20)->default('active')->index();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->text('description')->nullable();
            $table->boolean('featured')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('shop_customers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 40)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('shop_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('number', 30)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('shop_customers')->nullOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->string('payment_method', 30)->default('card');
            $table->decimal('total', 12, 2);
            $table->timestamp('placed_at')->index();
            $table->text('notes')->nullable();
            $table->json('items')->nullable();
            $table->timestamps();
        });

        Schema::create('blog_authors', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->text('bio')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('blog_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_employees', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('department', 80)->index();
            $table->string('employment_type', 20)->default('full-time');
            $table->string('status', 20)->default('active')->index();
            $table->date('hire_date');
            $table->decimal('salary', 12, 2)->nullable();
            $table->json('skills')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_departments', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('parent')->nullable();
            $table->string('head')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
        });

        Schema::create('hr_tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('project')->nullable();
            $table->string('assignee')->nullable();
            $table->string('priority', 20)->default('normal')->index();
            $table->string('status', 20)->default('todo')->index();
            $table->date('due_date')->nullable();
            $table->decimal('estimate', 8, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('hr_timesheets', function (Blueprint $table): void {
            $table->id();
            $table->string('employee');
            $table->string('project')->nullable();
            $table->date('work_date');
            $table->decimal('hours', 6, 2);
            $table->string('status', 20)->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_projects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('status', 20)->default('planned')->index();
            $table->string('owner');
            $table->decimal('budget', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->json('plan')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_leave_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('employee');
            $table->string('type', 30);
            $table->string('status', 20)->default('pending')->index();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_expenses', function (Blueprint $table): void {
            $table->id();
            $table->string('employee');
            $table->string('category', 40);
            $table->string('status', 20)->default('submitted')->index();
            $table->decimal('amount', 12, 2);
            $table->timestamp('submitted_at')->index();
            $table->timestamp('approved_at')->nullable();
            $table->json('line_items')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_expenses');
        Schema::dropIfExists('hr_timesheets');
        Schema::dropIfExists('hr_tasks');
        Schema::dropIfExists('hr_departments');
        Schema::dropIfExists('hr_leave_requests');
        Schema::dropIfExists('hr_projects');
        Schema::dropIfExists('hr_employees');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('blog_authors');
        Schema::dropIfExists('shop_orders');
        Schema::dropIfExists('shop_customers');
        Schema::dropIfExists('shop_products');
        Schema::dropIfExists('shop_brands');
        Schema::dropIfExists('shop_product_categories');
    }
};
