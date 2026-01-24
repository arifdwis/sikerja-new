<?php

// Fix for PHP 8.0 environment - Raw SQL Execution
$pdo = new PDO('mysql:host=127.0.0.1;dbname=sikerja;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Starting Cleanup...\n";

// 1. Remove Duplicate Menus
// Delete all where title is 'Slider' or 'FAQ' or 'Klasifikasi' or 'Kategori' and Re-insert cleanly.

$masterDataId = $pdo->query("SELECT id FROM menus WHERE title = 'Master Data' LIMIT 1")->fetchColumn();

if ($masterDataId) {
    echo "Deleting existing Slider/FAQ/Klasifikasi/Kategori menus to prevent duplicates...\n";
    $pdo->prepare("DELETE FROM menus WHERE title IN ('Slider', 'FAQ', 'Klasifikasi', 'Kategori') AND parent_id = ?")
        ->execute([$masterDataId]);

    echo "Re-inserting clean menus...\n";

    // Slider
    $pdo->prepare("INSERT IGNORE INTO menus (title, route, icon, parent_id, `order`, is_active, created_at, updated_at) VALUES ('Slider', 'master.slider.index', 'solar:slider-minimalistic-horizontal-broken', ?, 4, 1, NOW(), NOW())")
        ->execute([$masterDataId]);

    // FAQ
    $pdo->prepare("INSERT IGNORE INTO menus (title, route, icon, parent_id, `order`, is_active, created_at, updated_at) VALUES ('FAQ', 'master.faq.index', 'solar:question-circle-broken', ?, 5, 1, NOW(), NOW())")
        ->execute([$masterDataId]);

    // Klasifikasi (was Kategori)
    $pdo->prepare("INSERT IGNORE INTO menus (title, route, icon, parent_id, `order`, is_active, created_at, updated_at) VALUES ('Klasifikasi', 'master.kategori.index', 'solar:tag-linear', ?, 1, 1, NOW(), NOW())")
        ->execute([$masterDataId]);

    // Laman
    $pdo->prepare("INSERT IGNORE INTO menus (title, route, icon, parent_id, `order`, is_active, created_at, updated_at) VALUES ('Laman', 'master.laman.index', 'solar:file-text-bold', ?, 6, 1, NOW(), NOW())")
        ->execute([$masterDataId]);

    echo "Menus cleaned and re-inserted.\n";
}

// 2. Fix 403 Forbidden - Re-verify Permissions
$permissions = [
    'master.slider.index',
    'master.slider.create',
    'master.slider.edit',
    'master.slider.destroy',
    'master.faq.index',
    'master.faq.create',
    'master.faq.edit',
    'master.faq.destroy',
    'master.kategori.index',
    'master.kategori.create',
    'master.kategori.edit',
    'master.kategori.destroy',
    'master.laman.index',
    'master.laman.create',
    'master.laman.edit',
    'master.laman.destroy',
];

// Check struct again for guard_name
$checkGuard = $pdo->query("SHOW COLUMNS FROM permissions LIKE 'guard_name'")->fetch();
$hasGuard = $checkGuard ? true : false;

$stmt = $pdo->prepare("INSERT IGNORE INTO permissions (name, " . ($hasGuard ? "guard_name, " : "") . "created_at, updated_at) VALUES (?, " . ($hasGuard ? "'web', " : "") . "NOW(), NOW())");

foreach ($permissions as $perm) {
    $stmt->execute([$perm]);
}

// 3. Assign to Permissions (Re-run)
$roleStmt = $pdo->prepare("SELECT id FROM roles WHERE name IN ('administrator', 'super-admin', 'superadmin')");
$roleStmt->execute();
$roleIds = $roleStmt->fetchAll(PDO::FETCH_COLUMN);

$permStmt = $pdo->prepare("SELECT id FROM permissions WHERE name IN ('" . implode("','", $permissions) . "')");
$permStmt->execute();
$permIds = $permStmt->fetchAll(PDO::FETCH_COLUMN);

$assignStmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (permission_id, role_id) VALUES (?, ?)");

foreach ($roleIds as $roleId) {
    foreach ($permIds as $permId) {
        $assignStmt->execute([$permId, $roleId]);
    }
}
echo "Permissions re-assigned.\n";

// 4. Clear Cache (Manual Attempt)
// If file cache, we can try to delete the file.
// Assuming file driver based on .env
echo "Attempting to clear file cache...\n";
$cachePath = __DIR__ . '/storage/framework/cache/data';
if (is_dir($cachePath)) {
    // Basic recursive delete or just warn user
    echo "Please run 'php artisan cache:clear' if possible, or manually delete storage/framework/cache/data contents.\n";
    // We can try to unlink everything in a simple way
    $files = glob($cachePath . '/*');
    // ... logic to delete ...
}

// Try to update users table updated_at to force some refresh? No.
// Maybe Permission Cache Key?
// Spatie caches under `spatie.permission.cache` key.
// We can try to delete the cache file directly if we can find it.
// Default location for file cache is storage/framework/cache/data/ ...

// Manually delete Spatie cache entries from cache table if it exists?
// User said tables include `cache`.
$checkCacheTable = $pdo->query("SHOW TABLES LIKE 'cache'")->fetch();
if ($checkCacheTable) {
    $pdo->exec("DELETE FROM cache WHERE `key` LIKE '%spatie.permission.cache%'");
    echo "Deleted Spatie permission cache from DB.\n";
} else {
    echo "Cache table not found, assuming file cache.\n";
}

echo "Done.\n";
