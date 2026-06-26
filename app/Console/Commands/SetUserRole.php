<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetUserRole extends Command
{
    protected $signature = 'user:set-role {email} {role : admin, staff, or none (revoke panel access)}';

    protected $description = 'Grant or revoke admin-panel access and set a user role.';

    public function handle(): int
    {
        $role = strtolower((string) $this->argument('role'));

        if (! in_array($role, ['admin', 'staff', 'none'], true)) {
            $this->error('Role must be one of: admin, staff, none.');

            return self::FAILURE;
        }

        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user found with that email.');

            return self::FAILURE;
        }

        if ($role === 'none') {
            $user->forceFill(['is_admin' => false])->save();
            $this->info("Revoked panel access for {$user->email}.");
        } else {
            $user->forceFill(['is_admin' => true, 'role' => $role])->save();
            $this->info("{$user->email} is now '{$role}' with panel access.");
        }

        return self::SUCCESS;
    }
}
