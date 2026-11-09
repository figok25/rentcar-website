<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Membuat akun admin awal dari ADMIN_NAME, ADMIN_EMAIL, dan ADMIN_PASSWORD di .env.
     * Jika ADMIN_PASSWORD kosong, dibuat password acak yang ditampilkan sekali di terminal.
     * Jika akun sudah ada, password-nya tidak diubah.
     */
    public function run(): void
    {
        $name = (string) env('ADMIN_NAME', 'Administrator');
        $email = (string) env('ADMIN_EMAIL', 'admin@example.com');
        $password = (string) env('ADMIN_PASSWORD', 'password');

        $admin = User::query()->where('email', $email)->first();

        if ($admin) {
            $this->command?->info("Akun admin {$email} sudah ada, tidak diubah.");

            return;
        }

        $generated = $password === '';

        if ($generated) {
            $password = Str::password(16, symbols: false);
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ]);

        $this->command?->info("Akun admin dibuat: {$email}");

        if ($generated) {
            $this->command?->warn("Password acak (catat sekarang, tidak ditampilkan lagi): {$password}");
        }
    }
}
