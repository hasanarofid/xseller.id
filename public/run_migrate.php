<?php

use Illuminate\Contracts\Console\Kernel;
use App\Models\Product;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

try {
    // 1. Run Composer dump-autoload if shell execution is supported
    $composerLog = '';
    if (function_exists('shell_exec')) {
        $output = @shell_exec('composer dump-autoload 2>&1');
        if ($output) {
            $composerLog = "Composer Output:\n" . $output . "\n\n";
        }
    }

    // 2. Check if ?fresh=1 parameter is explicitly passed with confirm=yes
    $isFresh = isset($_GET['fresh']) && $_GET['fresh'] === '1' && isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

    // Allowed artisan commands that can be triggered via ?cmd=xxx
    $allowedCommands = [
        'migrate' => [
            'command' => 'migrate',
            'label'   => 'Run Pending Database Migrations (Aman & Tidak Reset Data)',
        ],
        'clear-cache' => [
            'command' => 'optimize:clear',
            'label'   => 'Clear & Rebuild All Application Caches',
        ],
        'fix-ro-matching' => [
            'command' => 'fix:ro-matching-bonus',
            'label'   => 'Fix Matching Bonus RO Sponsor',
        ],
        'fix-personal-rewards' => [
            'command' => 'fix:personal-rewards',
            'label'   => 'Fix Personal RO & PO Rewards',
        ],
        'fix-incentive-bonus' => [
            'command' => 'fix:incentive-bonus',
            'label'   => 'Fix Incentive Promotion Bonus',
        ],
        'seed-products' => [
            'command' => 'db:seed',
            'label'   => 'Update/Sinkronisasi Katalog Produk RO & PO (ProductSeeder)',
            'class'   => 'ProductSeeder',
        ],
        'reset-data' => [
            'command' => 'reset:system-data',
            'label'   => 'Reset Total Data Member & Transaksi (Kecuali Admin, Yayan, Arif & Produk)',
        ],
    ];

    $cmdKey = $_GET['cmd'] ?? null;

    if ($cmdKey && isset($allowedCommands[$cmdKey])) {
        // Run specific artisan command
        $cmdConfig = $allowedCommands[$cmdKey];
        $args = [];

        if (in_array($cmdConfig['command'], ['db:seed', 'migrate', 'migrate:fresh', 'fix:ro-matching-bonus', 'fix:personal-rewards', 'fix:incentive-bonus', 'reset:system-data'])) {
            $args['--force'] = true;
        }

        if (!empty($cmdConfig['class'])) {
            $args['--class'] = $cmdConfig['class'];
        }

        // Pass username argument if provided
        if (!empty($_GET['username'])) {
            $args['member_username'] = trim($_GET['username']);
        }

        $kernel->call($cmdConfig['command'], $args);
        $cmdOutput = $kernel->output();
        $action = $cmdConfig['label'] . (!empty($args['member_username']) ? " (@{$args['member_username']})" : '');

        // Fetch current active products summary
        $roCount = Product::where('type', 'ro')->count();
        $poCount = Product::where('type', 'po')->count();
        $allProducts = Product::orderBy('type')->get();

        echo "<!DOCTYPE html><html><head><title>{$action} - XSELLER</title><meta name='viewport' content='width=device-width, initial-scale=1'><style>body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;padding:2rem;background:#f8fafc;color:#1e293b;}.card{background:#fff;padding:2rem;border-radius:16px;box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);max-width:900px;margin:auto;}h1{margin-top:0;font-size:1.5rem;}pre{background:#0f172a;color:#38bdf8;padding:1.2rem;border-radius:10px;overflow-x:auto;font-size:13px;line-height:1.5;}table{width:100%;border-collapse:collapse;margin-top:1rem;}th,td{padding:10px 12px;border:1px solid #e2e8f0;text-align:left;font-size:13px;}th{background:#f1f5f9;color:#475569;}.btn{display:inline-block;padding:8px 16px;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;margin-right:8px;margin-bottom:8px;transition:all 0.2s;}.btn-primary{background:#0284c7;color:#fff;}.btn-success{background:#10b981;color:#fff;}.btn-secondary{background:#64748b;color:#fff;}</style></head><body>";
        echo "<div class='card'>";
        echo "<h1 style='color:#10b981;'>✓ Selesai: {$action}</h1>";
        echo "<pre>" . htmlspecialchars($cmdOutput ?: "Perintah berhasil dijalankan tanpa pesan error.") . "</pre>";
        
        echo "<h3 style='margin-top:1.5rem;'>Katalog Produk Aktif di Database ($roCount Produk RO, $poCount Produk PO):</h3>";
        echo "<table><thead><tr><th>Tipe</th><th>Nama Produk</th><th>Harga</th><th>Isi/Qty</th><th>Poin</th></tr></thead><tbody>";
        foreach ($allProducts as $p) {
            echo "<tr><td><strong style='color:" . ($p->type === 'ro' ? '#b45309' : '#0369a1') . ";'>" . strtoupper($p->type) . "</strong></td><td>" . htmlspecialchars($p->name) . "</td><td>Rp " . number_format($p->price, 0, ',', '.') . "</td><td>" . $p->quantity . "</td><td>" . $p->points . " Poin</td></tr>";
        }
        echo "</tbody></table>";

        echo "<div style='margin-top:24px;border-top:1px solid #e2e8f0;padding-top:16px;'>";
        echo "<h4 style='margin-top:0;color:#64748b;'>Perintah Lainnya:</h4>";
        echo "<a href='/run_migrate.php' class='btn btn-primary'>Jalankan Migration Standar</a>";
        echo "<a href='/run_migrate.php?cmd=clear-cache' class='btn btn-secondary'>Clear Cache</a>";
        echo "<a href='/run_migrate.php?cmd=seed-products' class='btn btn-secondary'>Update Seeder Produk</a>";
        echo "<a href='/admin/repeat-order' class='btn btn-success'>Buka Halaman Repeat Order</a>";
        echo "</div>";

        echo "</div></body></html>";

    } elseif ($isFresh) {
        $kernel->call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);
        $action = "Migrate Fresh & Seed";

        // Clear & rebuild application caches
        @$kernel->call('optimize:clear');

        echo "<!DOCTYPE html><html><head><title>Migration Fresh - XSELLER</title><style>body{font-family:sans-serif;padding:2rem;background:#f8fafc;color:#1e293b;}.card{background:#fff;padding:2rem;border-radius:16px;box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);max-width:800px;margin:auto;}pre{background:#0f172a;color:#38bdf8;padding:1.2rem;border-radius:10px;overflow-x:auto;}</style></head><body>";
        echo "<div class='card'>";
        echo "<h1 style='color:#ef4444;'>✓ SELESAI: {$action}</h1>";
        echo "<pre>" . htmlspecialchars($composerLog . ($kernel->output() ?: "Database telah di-fresh dan di-seed ulang.")) . "</pre>";
        echo "<p style='margin-top:20px;'><a href='/admin/dashboard' style='display:inline-block;padding:10px 18px;background:#0284c7;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Buka Dashboard Admin</a></p>";
        echo "</div></body></html>";

    } else {
        // DEFAULT BEHAVIOR: ONLY run 'migrate --force' WITHOUT resetting or deleting any data!
        $kernel->call('migrate', [
            '--force' => true,
        ]);
        $migrateLog = $kernel->output();

        // Clear & rebuild application caches
        @$kernel->call('config:clear');
        @$kernel->call('route:clear');
        @$kernel->call('view:clear');

        $action = "Database Migration (Aman - Tanpa Reset Data)";

        $roCount = Product::where('type', 'ro')->count();
        $poCount = Product::where('type', 'po')->count();
        $allProducts = Product::orderBy('type')->get();

        echo "<!DOCTYPE html><html><head><title>Migration Runner - XSELLER</title><meta name='viewport' content='width=device-width, initial-scale=1'><style>body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;padding:2rem;background:#f8fafc;color:#1e293b;}.card{background:#fff;padding:2rem;border-radius:16px;box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);max-width:900px;margin:auto;}h1{margin-top:0;font-size:1.5rem;}pre{background:#0f172a;color:#38bdf8;padding:1.2rem;border-radius:10px;overflow-x:auto;font-size:13px;line-height:1.5;}table{width:100%;border-collapse:collapse;margin-top:1rem;}th,td{padding:10px 12px;border:1px solid #e2e8f0;text-align:left;font-size:13px;}th{background:#f1f5f9;color:#475569;}.btn{display:inline-block;padding:8px 16px;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;margin-right:8px;margin-bottom:8px;transition:all 0.2s;}.btn-primary{background:#0284c7;color:#fff;}.btn-success{background:#10b981;color:#fff;}.btn-warning{background:#f59e0b;color:#fff;}.btn-secondary{background:#64748b;color:#fff;}.note-box{background:#f0fdf4;border:1px solid #bbf7d0;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;color:#166534;}</style></head><body>";
        echo "<div class='card'>";
        echo "<h1 style='color:#10b981;'>✓ SUCCESS: {$action}</h1>";
        echo "<div class='note-box'><strong>Data Aman:</strong> Mode ini hanya mengeksekusi migration tabel baru atau perubahan kolom tanpa menghapus data produk atau transaksi yang sudah ada.</div>";
        echo "<pre>" . htmlspecialchars($composerLog . ($migrateLog ?: "Database migration is up to date.\nApplication caches cleared successfully.")) . "</pre>";
        
        echo "<h3 style='margin-top:1.5rem;'>Katalog Produk Saat Ini ($roCount Produk RO, $poCount Produk PO):</h3>";
        echo "<table><thead><tr><th>Tipe</th><th>Nama Produk</th><th>Harga</th><th>Isi/Qty</th><th>Poin</th></tr></thead><tbody>";
        foreach ($allProducts as $p) {
            echo "<tr><td><strong style='color:" . ($p->type === 'ro' ? '#b45309' : '#0369a1') . ";'>" . strtoupper($p->type) . "</strong></td><td>" . htmlspecialchars($p->name) . "</td><td>Rp " . number_format($p->price, 0, ',', '.') . "</td><td>" . $p->quantity . "</td><td>" . $p->points . " Poin</td></tr>";
        }
        echo "</tbody></table>";

        echo "<div style='margin-top:24px;border-top:1px solid #e2e8f0;padding-top:16px;'>";
        echo "<h4 style='margin-top:0;color:#64748b;'>Pintasan Perintah Tambahan (Parameter URL):</h4>";
        echo "<a href='/run_migrate.php?cmd=clear-cache' class='btn btn-secondary'>Clear Cache (?cmd=clear-cache)</a>";
        echo "<a href='/run_migrate.php?cmd=seed-products' class='btn btn-warning'>Sync Katalog Produk (?cmd=seed-products)</a>";
        echo "<a href='/run_migrate.php?cmd=fix-ro-matching' class='btn btn-secondary'>Fix Matching RO (?cmd=fix-ro-matching)</a>";
        echo "<a href='/run_migrate.php?cmd=fix-personal-rewards' class='btn btn-secondary'>Fix Personal Rewards (?cmd=fix-personal-rewards)</a>";
        echo "<a href='/admin/repeat-order' class='btn btn-success'>Buka Halaman Repeat Order</a>";
        echo "</div>";

        echo "</div></body></html>";
    }
} catch (\Throwable $e) {
    echo "<!DOCTYPE html><html><head><title>Migration Error - XSELLER</title><style>body{font-family:sans-serif;padding:2rem;background:#f8fafc;}.card{background:#fff;padding:2rem;border-radius:16px;box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);max-width:800px;margin:auto;}pre{background:#0f172a;color:#f87171;padding:1.2rem;border-radius:10px;overflow-x:auto;}</style></head><body>";
    echo "<div class='card'>";
    echo "<h1 style='color:#ef4444;'>✕ ERROR: Migration Failed</h1>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div></body></html>";
}
