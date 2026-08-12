<?php

namespace Tests\Feature;

use Inlay\Forms\Form;
use Inlay\Panel;
use Inlay\Tables\Table;
use Inlay\Validation\ValidationRunner;
use Tests\TestCase;

class InlayPackagesTest extends TestCase
{
    public function test_non_cms_inlay_package_contracts_are_installed(): void
    {
        $this->assertTrue(class_exists(Form::class));
        $this->assertTrue(class_exists(Table::class));
        $this->assertTrue(class_exists(Panel::class));
        $this->assertTrue(class_exists(\Inlay\Resources\Resource::class));
        $this->assertTrue(class_exists(ValidationRunner::class));
    }

    public function test_media_migration_uses_a_mysql_safe_composite_key(): void
    {
        $migration = file_get_contents(database_path('migrations/2026_08_12_103953_create_inlay_media_tables.php'));

        $this->assertStringContainsString('$table->string(\'disk\', 50);', $migration);
        $this->assertStringContainsString('$table->string(\'path\', 500);', $migration);
        $this->assertStringNotContainsString('$table->string(\'path\', 1024);', $migration);
    }
}
