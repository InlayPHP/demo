<?php

namespace App\Inlay\Forms;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Form;
use Inlay\Forms\FormPage;

final class CreateDemoUser extends FormPage
{
    protected static string $component = 'demo/form';

    protected function form(Form $form): Form
    {
        return $form
            ->submitLabel('Create user')
            ->schema([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
            ]);
    }

    protected function submit(array $data, Request $request): RedirectResponse
    {
        User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Str::random(40),
        ]);

        return back()->with('success', 'User created from an Inlay form.');
    }
}
