<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetPanelAdminPassword extends Command
{
    protected $signature = 'panel:admin:reset-password';
    protected $description = 'Reset the single panel administrator password';

    public function handle(): int
    {
        $admin = User::query()->where('is_admin', true)->first();
        if (!$admin) {
            $this->error('No administrator exists. Run panel:admin:create first.');
            return self::FAILURE;
        }

        $password = (string) $this->secret('New password (at least 14 characters)');
        $confirm = (string) $this->secret('Confirm new password');
        if (strlen($password) < 14 || !hash_equals($password, $confirm)) {
            $this->error('Passwords must match and contain at least 14 characters.');
            return self::FAILURE;
        }

        $admin->forceFill(['password' => Hash::make($password)])->save();
        $this->info('Administrator password updated.');
        return self::SUCCESS;
    }
}
