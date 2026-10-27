<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ProductionOwnerSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->where('role', 'owner')->exists()) {
            return;
        }

        $name = env('BOOTSTRAP_OWNER_NAME');
        $username = env('BOOTSTRAP_OWNER_USERNAME');
        $email = env('BOOTSTRAP_OWNER_EMAIL');
        $password = env('BOOTSTRAP_OWNER_PASSWORD');

        if (! $name || ! $username || ! filter_var($email, FILTER_VALIDATE_EMAIL) || ! $password || strlen($password) < 12) {
            throw new RuntimeException('Set valid bootstrap owner details and a password with at least 12 characters.');
        }

        User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'owner',
            'status' => 'active',
        ]);
    }
}
