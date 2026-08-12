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
}
