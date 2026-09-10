<?php

use Illuminate\Contracts\Console\Kernel;

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
    ];

    $cmdKey = $_GET['cmd'] ?? null;

    if ($cmdKey && isset($allowedCommands[$cmdKey])) {
        // Run specific artisan command
        $cmdConfig = $allowedCommands[$cmdKey];
        $args = [];

        // Pass username argument if provided
        if (!empty($_GET['username'])) {
            $args['member_username'] = trim($_GET['username']);
        }

        $kernel->call($cmdConfig['command'], array_merge($args, ['--force' => true]));
        $action = $cmdConfig['label'] . (!empty($args['member_username']) ? " (@{$args['member_username']})" : '');

        echo "<!DOCTYPE html><html><head><title>{$action} - XSELLER</title><style>body{font-family:sans-serif;padding:2rem;background:#f4f6f9;color:#333;}.card{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);max-width:800px;margin:auto;}h1{margin-top:0;}pre{background:#1e293b;color:#38bdf8;padding:1rem;border-radius:8px;overflow-x:auto;}</style></head><body>";
        echo "<div class='card'>";
        echo "<h1 style='color:#10b981;'>✓ SUCCESS: {$action}</h1>";
        echo "<pre>" . htmlspecialchars($kernel->output() ?: "Command selesai tanpa output.") . "</pre>";
        echo "<p style='margin-top:20px;'><a href='/admin/repeat-order' style='display:inline-block;padding:10px 18px;background:#10b981;color:#fff;text-decoration:none;border-radius:8px;font-weight:bold;'>Buka Halaman Repeat Order</a></p>";
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
        $action = "Migrate (Update Only)";

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
    }
} catch (\Throwable $e) {
    echo "<!DOCTYPE html><html><head><title>Migration Error - XSELLER</title><style>body{font-family:sans-serif;padding:2rem;background:#f4f6f9;}.card{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.1);max-width:800px;margin:auto;}pre{background:#1e293b;color:#f87171;padding:1rem;border-radius:8px;overflow-x:auto;}</style></head><body>";
    echo "<div class='card'>";
    echo "<h1 style='color:#ef4444;'>✕ ERROR: Migration Failed</h1>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div></body></html>";
}
