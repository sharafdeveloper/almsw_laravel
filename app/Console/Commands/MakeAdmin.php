<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    /**
     * Usage:
     *   php artisan user:make-admin                 (lists users, asks which to promote)
     *   php artisan user:make-admin you@example.com (promotes that email directly)
     */
    protected $signature = 'user:make-admin {email? : The email of the user to promote}';

    protected $description = 'Promote a user to Admin role';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (! $email) {
            $users = User::orderBy('id')->get(['id', 'name', 'email', 'role']);
            if ($users->isEmpty()) {
                $this->error('No users found. Run "php artisan db:seed" first.');
                return self::FAILURE;
            }

            $this->table(
                ['ID', 'Name', 'Email', 'Role'],
                $users->map(fn ($u) => [
                    $u->id,
                    $u->name,
                    $u->email,
                    $u->role == User::ROLE_ADMIN ? 'Admin' : 'Employee',
                ])->toArray()
            );

            $email = $this->ask('Enter the email of the user you want to make Admin');
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("No user found with email: {$email}");
            return self::FAILURE;
        }

        $user->role = User::ROLE_ADMIN;
        $user->save();

        $this->info("✔ {$user->name} ({$user->email}) is now an ADMIN. Log out and log back in to see all menus.");
        return self::SUCCESS;
    }
}
