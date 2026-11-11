<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreatePanelAdmin extends Command
{
    protected $signature = 'panel:admin:create';
    protected $description = 'Create the single administrator account for the management panel';

    public function handle(): int
    {
        if (User::query()->where('is_admin', true)->exists()) {
            $this->error('An administrator already exists. Use panel:admin:reset-password to change its password.');
            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Administrator name', 'Administrator'));
        $email = strtolower(trim((string) $this->ask('Administrator email')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Please enter a valid email address.');
            return self::FAILURE;
        }

        $password = (string) $this->secret('Password (at least 14 characters)');
        $confirm = (string) $this->secret('Confirm password');
        if (strlen($password) < 14 || !hash_equals($password, $confirm)) {
            $this->error('Passwords must match and contain at least 14 characters.');
            return self::FAILURE;
        }

        User::query()->create([
            'name' => $name !== '' ? $name : 'Administrator',
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        $this->info('Administrator account created.');
        return self::SUCCESS;
    }
}
