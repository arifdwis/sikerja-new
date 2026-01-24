<?php

// Fix for PHP 8.0 environment - Raw SQL Execution
$pdo = new PDO('mysql:host=127.0.0.1;dbname=sikerja;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Starting Manual Migration...\n";

// 1. Create Tables
$tables = [
    "CREATE TABLE IF NOT EXISTS sliders (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        uuid CHAR(36) NOT NULL UNIQUE,
        title VARCHAR(255) NOT NULL,
        `desc` TEXT NULL,
        image VARCHAR(255) NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS faqs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        uuid CHAR(36) NOT NULL UNIQUE,
        question VARCHAR(255) NOT NULL,
        answer TEXT NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS kategori (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        uuid CHAR(36) NOT NULL UNIQUE,
        label VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $sql) {
    $pdo->exec($sql);
    echo "Table verified/created.\n";
}

// 2. Insert Permissions
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
];

// Check if guard_name exists in permissions table
$checkGuard = $pdo->query("SHOW COLUMNS FROM permissions LIKE 'guard_name'")->fetch();
$hasGuard = $checkGuard ? true : false;

$sql = "INSERT IGNORE INTO permissions (name, " . ($hasGuard ? "guard_name, " : "") . "created_at, updated_at) VALUES (?, " . ($hasGuard ? "'web', " : "") . "NOW(), NOW())";
$stmt = $pdo->prepare($sql);

foreach ($permissions as $perm) {
    if ($hasGuard) {
        $stmt->execute([$perm]);
    } else {
        $stmt->execute([$perm]);
    }
}
echo "Permissions inserted.\n";

// 3. Assign to Roles (Fetch IDs first)
$roleStmt = $pdo->prepare("SELECT id FROM roles WHERE name IN ('administrator', 'super-admin', 'superadmin')");
$roleStmt->execute();
$roleIds = $roleStmt->fetchAll(PDO::FETCH_COLUMN);

$permStmt = $pdo->prepare("SELECT id FROM permissions WHERE name IN ('" . implode("','", $permissions) . "')");
$permStmt->execute();
$permIds = $permStmt->fetchAll(PDO::FETCH_COLUMN);

// Check columns of role_permissions to be sure
$checkCols = $pdo->query("SHOW COLUMNS FROM role_permissions");
$cols = $checkCols->fetchAll(PDO::FETCH_COLUMN);

echo "Columns in role_permissions: " . implode(', ', $cols) . "\n";

// Assuming role_id and permission_id exist
$assignStmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (permission_id, role_id) VALUES (?, ?)");

foreach ($roleIds as $roleId) {
    foreach ($permIds as $permId) {
        $assignStmt->execute([$permId, $roleId]);
    }
}
echo "Permissions assigned to roles " . implode(',', $roleIds) . ".\n";

// 4. Create Menus
$masterDataId = $pdo->query("SELECT id FROM menus WHERE title = 'Master Data' LIMIT 1")->fetchColumn();

if ($masterDataId) {
    // Slider
    $pdo->prepare("INSERT IGNORE INTO menus (title, route, icon, parent_id, `order`, is_active, created_at, updated_at) VALUES ('Slider', 'master.slider.index', 'solar:slider-minimalistic-horizontal-broken', ?, 4, 1, NOW(), NOW())")
        ->execute([$masterDataId]);

    // FAQ
    $pdo->prepare("INSERT IGNORE INTO menus (title, route, icon, parent_id, `order`, is_active, created_at, updated_at) VALUES ('FAQ', 'master.faq.index', 'solar:question-circle-broken', ?, 5, 1, NOW(), NOW())")
        ->execute([$masterDataId]);

    echo "Menus added.\n";
} else {
    echo "Master Data menu not found. Skipping menu insertion.\n";
}

// 5. Update Kategori -> Klasifikasi
$pdo->exec("UPDATE menus SET title = 'Klasifikasi' WHERE title = 'Kategori'");
echo "Menu renamed to Klasifikasi.\n";

echo "Done.\n";
