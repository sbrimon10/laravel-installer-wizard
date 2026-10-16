<?php

namespace Sbrimon10\InstallerWizard\Support;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminManager
{
    public function model(): string
    {
        return config(
            'installer-wizard.admin_model',
            config('auth.providers.users.model', 'App\\Models\\User')
        );
    }

    public function tableExists(): bool
    {
        try {
            return Schema::hasTable('users');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @param  array{name: string, email: string, password: string}  $data
     * @return array{ok: bool, message: string}
     */
    public function create(array $data): array
    {
        $model = $this->model();

        if (! class_exists($model)) {
            return ['ok' => false, 'message' => "Admin model [{$model}] not found."];
        }

        if (! $this->tableExists()) {
            return ['ok' => false, 'message' => 'Users table not found. Run migrations first.'];
        }

        try {
            $user = $model::where('email', $data['email'])->first();

            if ($user) {
                $user->forceFill([
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                ])->save();
            } else {
                $user = $model::forceCreate([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]);
            }

            return ['ok' => true, 'message' => "Admin [{$user->email}] ready."];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Admin creation failed: '.$e->getMessage()];
        }
    }
}
