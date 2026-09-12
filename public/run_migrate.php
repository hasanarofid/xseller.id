<?php

use Illuminate\Contracts\Console\Kernel;
use App\Models\Product;

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

try {
    // 1. Run Composer dump-autoload / install if shell execution is supported
    $composerLog = '';
    if (function_exists('shell_exec')) {
        $output = @shell_exec('composer dump-autoload 2>&1');
        if ($output) {
            $composerLog = "Composer Output:\n" . $output . "\n\n";
        }
    }

    // 2. Check if ?fresh=1 parameter is explicitly passed
    $isFresh = isset($_GET['fresh']) && $_GET['fresh'] === '1';

    // Allowed artisan commands that can be triggered via ?cmd=xxx
    $allowedCommands = [
        'fix-ro-matching' => [
            'command' => 'fix:ro-matching-bonus',
            'label'   => 'Fix Matching Bonus RO',
        ],
        'fix-incentive-bonus' => [
            'command' => 'fix:incentive-bonus',
            'label'   => 'Fix Incentive Promotion Bonus',
        ],
        'fix-personal-rewards' => [
            'command' => 'fix:personal-rewards',
            'label'   => 'Fix Personal RO & PO Rewards',
        ],
        'seed-products' => [
            'command' => 'db:seed',
            'label'   => 'Update Katalog Produk RO & PO',
            'class'   => 'ProductSeeder',
        ],
    ];

    $cmdKey = $_GET['cmd'] ?? null;

    if ($cmdKey && isset($allowedCommands[$cmdKey])) {
        // Run specific artisan command
        $cmdConfig = $allowedCommands[$cmdKey];
        $args = ['--force' => true];

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

        echo "<!DOCTYPE html><html><head><title>{$action} - XSELLER</title><style>body{font-family:sans-serif;padding:2rem;background:#f4f6f9;color:#333;}.card{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);max-width:800px;margin:auto;}h1{margin-top:0;}pre{background:#1e293b;color:#38bdf8;padding:1rem;border-radius:8px;overflow-x:auto;}table{width:100%;border-collapse:collapse;margin-top:1rem;}th,td{padding:8px 12px;border:1px solid #e2e8f0;text-align:left;}th{background:#f1f5f9;}</style></head><body>";
        echo "<div class='card'>";
        echo "<h1 style='color:#10b981;'>✓ SUCCESS: {$action}</h1>";
        echo "<pre>" . htmlspecialchars($cmdOutput ?: "Command selesai tanpa error.") . "</pre>";
        echo "<h3>Katalog Produk Saat Ini ($roCount Produk RO, $poCount Produk PO):</h3>";
        echo "<table><thead><tr><th>Tipe</th><th>Nama Produk</th><th>Harga</th><th>Isi/Qty</th><th>Poin</th></tr></thead><tbody>";
        foreach ($allProducts as $p) {
            echo "<tr><td><strong style='color:" . ($p->type === 'ro' ? '#5c3a21' : '#1653a1') . ";'>" . strtoupper($p->type) . "</strong></td><td>" . htmlspecialchars($p->name) . "</td><td>Rp " . number_format($p->price, 0, ',', '.') . "</td><td>" . $p->quantity . "</td><td>" . $p->points . " Poin</td></tr>";
        }
        echo "</tbody></table>";
        echo "<p style='margin-top:20px;'><a href='/admin/kelola-produk' style='display:inline-block;padding:10px 18px;background:#10b981;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Buka Halaman Kelola Produk</a></p>";
        echo "</div></body></html>";

    } elseif ($isFresh) {
        $kernel->call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);
        $action = "Migrate Fresh & Seed";

        // 3. Clear & rebuild application caches
        @$kernel->call('config:clear');
        @$kernel->call('route:clear');
        @$kernel->call('view:clear');

        echo "<!DOCTYPE html><html><head><title>Migration & Composer Runner - XSELLER</title><style>body{font-family:sans-serif;padding:2rem;background:#f4f6f9;color:#333;}.card{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);max-width:800px;margin:auto;}h1{margin-top:0;}pre{background:#1e293b;color:#38bdf8;padding:1rem;border-radius:8px;overflow-x:auto;}</style></head><body>";
        echo "<div class='card'>";
        echo "<h1 style='color:#10b981;'>✓ SUCCESS: {$action} Finished!</h1>";
        echo "<pre>" . htmlspecialchars($composerLog . ($kernel->output() ?: "Migration completed successfully with no pending migrations.")) . "</pre>";
        echo "<p style='margin-top:20px;'><a href='/admin/laporan' style='display:inline-block;padding:10px 18px;background:#10b981;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Buka Halaman Laporan</a></p>";
        echo "</div></body></html>";

    } else {
        $kernel->call('migrate', [
            '--force' => true,
        ]);
        $migrateLog = $kernel->output();
        
        // Auto seed ProductSeeder to ensure product catalog is updated
        $kernel->call('db:seed', [
            '--class' => 'ProductSeeder',
            '--force' => true,
        ]);
        $seedLog = $kernel->output();

        $action = "Migrate & Seed Catalog (Update Only)";

        // 3. Clear & rebuild application caches
        @$kernel->call('config:clear');
        @$kernel->call('route:clear');
        @$kernel->call('view:clear');

        $roCount = Product::where('type', 'ro')->count();
        $poCount = Product::where('type', 'po')->count();
        $allProducts = Product::orderBy('type')->get();

        echo "<!DOCTYPE html><html><head><title>Migration & Product Seeder - XSELLER</title><style>body{font-family:sans-serif;padding:2rem;background:#f4f6f9;color:#333;}.card{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);max-width:800px;margin:auto;}h1{margin-top:0;}pre{background:#1e293b;color:#38bdf8;padding:1rem;border-radius:8px;overflow-x:auto;}table{width:100%;border-collapse:collapse;margin-top:1rem;}th,td{padding:8px 12px;border:1px solid #e2e8f0;text-align:left;}th{background:#f1f5f9;}</style></head><body>";
        echo "<div class='card'>";
        echo "<h1 style='color:#10b981;'>✓ SUCCESS: {$action} Finished!</h1>";
        echo "<pre>" . htmlspecialchars($composerLog . ($migrateLog ?: "Database migration up-to-date.\n") . ($seedLog ?: "ProductSeeder executed successfully.")) . "</pre>";
        
        echo "<h3 style='margin-top:1.5rem;'>Katalog Produk Aktif di Database ($roCount Produk RO, $poCount Produk PO):</h3>";
        echo "<table><thead><tr><th>Tipe</th><th>Nama Produk</th><th>Harga</th><th>Isi/Qty</th><th>Poin</th></tr></thead><tbody>";
        foreach ($allProducts as $p) {
            echo "<tr><td><strong style='color:" . ($p->type === 'ro' ? '#5c3a21' : '#1653a1') . ";'>" . strtoupper($p->type) . "</strong></td><td>" . htmlspecialchars($p->name) . "</td><td>Rp " . number_format($p->price, 0, ',', '.') . "</td><td>" . $p->quantity . "</td><td>" . $p->points . " Poin</td></tr>";
        }
        echo "</tbody></table>";

        echo "<div style='margin-top:20px;display:flex;gap:10px;'>";
        echo "<a href='/admin/repeat-order' style='padding:10px 18px;background:#5c3a21;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Lihat Produk RO</a>";
        echo "<a href='/admin/purchase-order' style='padding:10px 18px;background:#1653a1;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Lihat Produk PO</a>";
        echo "<a href='/admin/kelola-produk' style='padding:10px 18px;background:#10b981;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Kelola Produk Admin</a>";
        echo "</div>";
        echo "</div></body></html>";
    }
} catch (\Throwable $e) {
    echo "<!DOCTYPE html><html><head><title>Migration Error - XSELLER</title><style>body{font-family:sans-serif;padding:2rem;background:#f4f6f9;}.card{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);max-width:800px;margin:auto;}pre{background:#1e293b;color:#f87171;padding:1rem;border-radius:8px;overflow-x:auto;}</style></head><body>";
    echo "<div class='card'>";
    echo "<h1 style='color:#ef4444;'>✕ ERROR: Migration Failed</h1>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div></body></html>";
}
