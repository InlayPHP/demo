<?php

declare(strict_types=1);

namespace App\Validation;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inlay\Validation\Validation;
use Inlay\Validation\ValidationContext;

final class BlogRules extends Validation
{
    public function rules(ValidationContext $context): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('blogs', 'slug')->ignore($context->record()),
            ],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:50000'],
            'published_at' => ['nullable', 'date'],
            'featured' => ['boolean'],
        ];
    }

    public function prepare(array $data, ValidationContext $context): array
    {
        foreach (['title', 'excerpt', 'body'] as $field) {
            if (array_key_exists($field, $data) && is_string($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }

        if (array_key_exists('slug', $data)) {
            $data['slug'] = Str::slug((string) $data['slug']);
        }

        if (array_key_exists('status', $data)) {
            $data['status'] = strtolower(trim((string) $data['status']));
        }

        $data['featured'] = (bool) ($data['featured'] ?? false);

        return $data;
    }
}
