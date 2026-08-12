<?php

declare(strict_types=1);

namespace App\Validation;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inlay\Validation\Validation;
use Inlay\Validation\ValidationContext;

final class UserRules extends Validation
{
    public function rules(ValidationContext $context): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($context->record()),
            ],
            'password' => $context->isOperation('create')
                ? ['required', 'string', 'min:8', 'max:255']
                : ['nullable', 'string', 'min:8', 'max:255'],
        ];
    }

    public function prepare(array $data, ValidationContext $context): array
    {
        if (isset($data['name'])) {
            $data['name'] = trim((string) $data['name']);
        }

        if (isset($data['email'])) {
            $data['email'] = Str::lower(trim((string) $data['email']));
        }

        return $data;
    }
}
