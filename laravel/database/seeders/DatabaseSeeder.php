<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (env('CLUBESTOQUE_ADMIN_EMAIL') && env('CLUBESTOQUE_ADMIN_PASSWORD')) {
            User::updateOrCreate(
                ['email' => env('CLUBESTOQUE_ADMIN_EMAIL')],
                [
                    'name' => env('CLUBESTOQUE_ADMIN_NAME', 'Gerente ClubEstoque'),
                    'role' => 'gerente',
                    'active' => true,
                    'password' => env('CLUBESTOQUE_ADMIN_PASSWORD'),
                ]
            );
        }
    }
}
