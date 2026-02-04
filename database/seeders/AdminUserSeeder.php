<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{

    public function run(): void
    {

        $adminEmail = 'mili@gmail.com';


        $adminPassword = 'albania07';

        $adminName = 'Admin';


        $user = User::where('email', $adminEmail)->first();

        if ($user) {

            $user->update([
                'role' => 'admin',
                'name' => $adminName,
            ]);

            $this->command->info('✅ User u be ADMIN!');
            $this->command->info("📧 Email: {$adminEmail}");
            $this->command->info("👤 Name: {$user->name}");
            $this->command->info("🔑 Password: (existing password unchanged)");
        } else {
            $admin = User::create([
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            $this->command->info('✅ Admin user u krijua me sukses!');
            $this->command->info("📧 Email: {$adminEmail}");
            $this->command->info("🔑 Password: {$adminPassword}");
            $this->command->info("👤 Name: {$adminName}");
            $this->command->warn('⚠️  NDRYSHO PASSWORD SAPO TE BËSH LOGIN!');
        }

        $this->command->line('');
        $this->command->line('═══════════════════════════════════════');
        $this->command->info('  Admin user setup completed!');
        $this->command->line('═══════════════════════════════════════');
    }
}
