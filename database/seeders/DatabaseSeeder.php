<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles and Permissions
        $this->call(RoleAndPermissionSeeder::class);
        $this->call(ProductSeeder::class);

        // 2. Seed Default Users and Assign Roles
        $admin = User::updateOrCreate(
            ['email' => 'admin@xseller.id'],
            [
                'name' => 'President Director (Admin)',
                'username' => 'admin',
                'password' => bcrypt('password'),
                'left_count' => 3,
                'right_count' => 2,
                'left_points' => 1,
                'right_points' => 0,
                'package_name' => 'Ultimate',
            ]
        );
        $admin->assignRole('admin');

        // Level 2 (Children of Admin)
        $budi = User::updateOrCreate(
            ['email' => 'budi@xseller.id'],
            [
                'name' => 'Budi Santoso',
                'username' => 'budi',
                'password' => bcrypt('password'),
                'parent_id' => $admin->id,
                'position' => 'left',
                'left_count' => 1,
                'right_count' => 1,
                'left_points' => 0,
                'right_points' => 0,
                'package_name' => 'Pro',
            ]
        );
        $budi->assignRole('client');

        $siti = User::updateOrCreate(
            ['email' => 'siti@xseller.id'],
            [
                'name' => 'Siti Rahma',
                'username' => 'siti',
                'password' => bcrypt('password'),
                'parent_id' => $admin->id,
                'position' => 'right',
                'left_count' => 1,
                'right_count' => 0,
                'left_points' => 0,
                'right_points' => 0,
                'package_name' => 'Medium',
            ]
        );
        $siti->assignRole('client');

        // Level 3 (Grandchildren)
        $dewi = User::updateOrCreate(
            ['email' => 'dewi@xseller.id'],
            [
                'name' => 'Dewi Lestari',
                'username' => 'dewi',
                'password' => bcrypt('password'),
                'parent_id' => $budi->id,
                'position' => 'left',
                'left_count' => 0,
                'right_count' => 0,
                'left_points' => 0,
                'right_points' => 0,
                'package_name' => 'Basic',
            ]
        );
        $dewi->assignRole('client');

        $eko = User::updateOrCreate(
            ['email' => 'eko@xseller.id'],
            [
                'name' => 'Eko Prasetyo',
                'username' => 'eko',
                'password' => bcrypt('password'),
                'parent_id' => $budi->id,
                'position' => 'right',
                'left_count' => 0,
                'right_count' => 0,
                'left_points' => 0,
                'right_points' => 0,
                'package_name' => 'Basic',
            ]
        );
        $eko->assignRole('client');

        $fajar = User::updateOrCreate(
            ['email' => 'fajar@xseller.id'],
            [
                'name' => 'Fajar Hidayat',
                'username' => 'fajar',
                'password' => bcrypt('password'),
                'parent_id' => $siti->id,
                'position' => 'left',
                'left_count' => 0,
                'right_count' => 0,
                'left_points' => 0,
                'right_points' => 0,
                'package_name' => 'Starter',
            ]
        );
        $fajar->assignRole('client');

        // 2b. Seed Active & Used Vouchers for Admin matching mockup
        $v1 = \App\Models\Voucher::updateOrCreate(
            ['code' => 'PIN-9812-XYZ'],
            [
                'user_id' => $admin->id,
                'package_name' => 'Basic',
                'status' => 'active',
                'created_at' => now()->subDays(5),
            ]
        );

        $v2 = \App\Models\Voucher::updateOrCreate(
            ['code' => 'PIN-4432-ABC'],
            [
                'user_id' => $admin->id,
                'package_name' => 'Basic',
                'status' => 'active',
                'created_at' => now()->subDays(5),
            ]
        );

        \App\Models\Voucher::updateOrCreate(
            ['code' => 'PIN-1234-MNO'],
            [
                'user_id' => $admin->id,
                'package_name' => 'Basic',
                'status' => 'used',
                'used_by_id' => $budi->id,
                'used_at' => now()->subDays(4),
                'created_at' => now()->subDays(5),
            ]
        );

        \App\Models\Voucher::updateOrCreate(
            ['code' => 'PIN-5678-PQR'],
            [
                'user_id' => $admin->id,
                'package_name' => 'Basic',
                'status' => 'used',
                'used_by_id' => $siti->id,
                'used_at' => now()->subDays(3),
                'created_at' => now()->subDays(5),
            ]
        );

        \App\Models\Voucher::updateOrCreate(
            ['code' => 'PIN-2222-BBB'],
            [
                'user_id' => $admin->id,
                'package_name' => 'Basic',
                'status' => 'used',
                'used_by_id' => $dewi->id,
                'used_at' => now()->subDays(1),
                'created_at' => now()->subDays(3),
            ]
        );

        // Seed Transfer History matching mockup
        $vt1 = \App\Models\Voucher::updateOrCreate(
            ['code' => 'PIN-5555-DDD'],
            [
                'user_id' => $budi->id,
                'package_name' => 'Basic',
                'status' => 'active',
                'created_at' => now()->subDays(2),
            ]
        );

        \App\Models\VoucherTransfer::updateOrCreate(
            ['voucher_code' => 'PIN-5555-DDD'],
            [
                'voucher_id' => $vt1->id,
                'sender_id' => $admin->id,
                'recipient_id' => $budi->id,
                'created_at' => now()->subDays(2)->setHour(21)->setMinute(20),
            ]
        );

        $vt2 = \App\Models\Voucher::updateOrCreate(
            ['code' => 'PIN-8888-EEE'],
            [
                'user_id' => $siti->id,
                'package_name' => 'Basic',
                'status' => 'active',
                'created_at' => now()->subDays(1),
            ]
        );

        \App\Models\VoucherTransfer::updateOrCreate(
            ['voucher_code' => 'PIN-8888-EEE'],
            [
                'voucher_id' => $vt2->id,
                'sender_id' => $admin->id,
                'recipient_id' => $siti->id,
                'created_at' => now()->subDays(1)->setHour(23)->setMinute(0),
            ]
        );

        // Seed Financial Wallet Transactions matching mockup
        \App\Models\WalletTransaction::updateOrCreate(
            ['description' => 'Transfer saldo modal awal', 'user_id' => $admin->id],
            [
                'type' => 'out',
                'category' => 'transfer',
                'amount' => 150000,
                'related_user_id' => $budi->id,
                'created_at' => now()->subDays(3)->setHour(10)->setMinute(15)->setSecond(0),
            ]
        );

        \App\Models\WalletTransaction::updateOrCreate(
            ['description' => 'Cairkan bonus sponsor ke E-Wallet', 'user_id' => $admin->id],
            [
                'type' => 'in',
                'category' => 'payout',
                'amount' => 300000,
                'created_at' => now()->subDays(2)->setHour(14)->setMinute(30)->setSecond(0),
            ]
        );

        // Seed Activity Bonus Logs matching mockup
        \App\Models\BonusLog::updateOrCreate(
            ['transaction_code' => 'B001'],
            [
                'user_id' => $admin->id,
                'category' => 'sponsor',
                'source_user_id' => $budi->id,
                'description' => 'Bonus Sponsor: Pendaftaran budi (USR002)',
                'amount' => 100000,
                'created_at' => now()->subDays(4)->setHour(18)->setMinute(30)->setSecond(0),
            ]
        );

        \App\Models\BonusLog::updateOrCreate(
            ['transaction_code' => 'B002'],
            [
                'user_id' => $admin->id,
                'category' => 'sponsor',
                'source_user_id' => $siti->id,
                'description' => 'Bonus Sponsor: Pendaftaran siti (USR003)',
                'amount' => 100000,
                'created_at' => now()->subDays(3)->setHour(16)->setMinute(15)->setSecond(0),
            ]
        );

        \App\Models\BonusLog::updateOrCreate(
            ['transaction_code' => 'B005'],
            [
                'user_id' => $admin->id,
                'category' => 'sponsor',
                'source_user_id' => $eko->id,
                'description' => 'Bonus Sponsor: Pendaftaran eko (USR005)',
                'amount' => 100000,
                'created_at' => now()->subDays(1)->setHour(23)->setMinute(0)->setSecond(0),
            ]
        );

        \App\Models\BonusLog::updateOrCreate(
            ['transaction_code' => 'P001'],
            [
                'user_id' => $admin->id,
                'category' => 'pasangan',
                'source_user_id' => $budi->id,
                'description' => 'Bonus Pasangan: Keseimbangan Kiri & Kanan',
                'amount' => 100000,
                'created_at' => now()->subDays(2)->setHour(12)->setMinute(0)->setSecond(0),
            ]
        );

        // 3. Seed Settings
        $this->call(SettingSeeder::class);

        // 4. Seed Pages and Sections
        $this->call(PageAndSectionSeeder::class);

        // 5. Seed Categories & Posts
        $general = Category::updateOrCreate(
            ['slug' => 'general'],
            ['name' => 'General']
        );

        $tech = Category::updateOrCreate(
            ['slug' => 'technology'],
            ['name' => 'Technology']
        );

        Post::updateOrCreate(
            ['slug' => 'selamat-datang-di-boilerplate-cms-baru-anda'],
            [
                'category_id' => $general->id,
                'title' => 'Selamat Datang di Boilerplate CMS Baru Anda',
                'content' => 'Ini adalah postingan pertama di CMS Anda. Anda dapat mengedit, menghapus, atau membuat postingan baru melalui dashboard admin dengan sangat mudah.',
                'image' => null,
                'status' => 'published',
                'is_featured' => true
            ]
        );

        Post::updateOrCreate(
            ['slug' => 'mengapa-laravel-11-dan-vue-3-sangat-powerful'],
            [
                'category_id' => $tech->id,
                'title' => 'Mengapa Laravel 11 dan Vue 3 Sangat Powerful?',
                'content' => 'Kombinasi Laravel dan Vue 3 yang dihubungkan oleh Inertia.js menciptakan pengalaman pengembangan Single Page Application (SPA) murni tanpa overhead penulisan API terpisah. Hal ini mempercepat siklus development secara signifikan.',
                'image' => null,
                'status' => 'published',
                'is_featured' => false
            ]
        );
    }
}
