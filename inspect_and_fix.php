<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=sikerja;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "--- Inspecting Menus ---\n";
$menus = $pdo->query("SELECT id, title, route, parent_id FROM menus WHERE title IN ('Klasifikasi', 'Kategori', 'Slider', 'FAQ')")
    ->fetchAll(PDO::FETCH_ASSOC);

foreach ($menus as $m) {
    echo "ID: {$m['id']}, Title: {$m['title']}, Route: {$m['route']}\n";

    // Fix Klasifikasi Route
    if ($m['title'] === 'Klasifikasi' && $m['route'] !== 'master.kategori.index') {
        echo " -> Fixing route for Klasifikasi...\n";
        $pdo->prepare("UPDATE menus SET route = 'master.kategori.index' WHERE id = ?")->execute([$m['id']]);
        echo " -> Fixed.\n";
    }
}

echo "\n--- Inspecting Permissions ---\n";
$role = $pdo->query("SELECT id, name FROM roles WHERE name = 'administrator'")->fetch(PDO::FETCH_ASSOC);
if ($role) {
    echo "Role Administrator ID: {$role['id']}\n";

    $perms = $pdo->prepare("SELECT p.name FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id WHERE rp.role_id = ?");
    $perms->execute([$role['id']]);
    $existingPerms = $perms->fetchAll(PDO::FETCH_COLUMN);

    $required = [
        'master.slider.index',
        'master.faq.index',
        'master.kategori.index'
    ];

    foreach ($required as $req) {
        if (in_array($req, $existingPerms)) {
            echo " [OK] $req\n";
        } else {
            echo " [MISSING] $req -> Adding...\n";
            // Find Permission ID
            $pId = $pdo->prepare("SELECT id FROM permissions WHERE name = ?");
            $pId->execute([$req]);
            $id = $pId->fetchColumn();

            if ($id) {
                $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)")
                    ->execute([$role['id'], $id]);
                echo " -> Added.\n";
            } else {
                echo " -> Permission ID not found for $req! Creating...\n";
                // Check if guard_name exists
                $checkGuard = $pdo->query("SHOW COLUMNS FROM permissions LIKE 'guard_name'")->fetch();
                $hasGuard = $checkGuard ? true : false;

                $sql = "INSERT INTO permissions (name, " . ($hasGuard ? "guard_name, " : "") . "created_at, updated_at) VALUES (?, " . ($hasGuard ? "'web', " : "") . "NOW(), NOW())";
                $pdo->prepare($sql)->execute([$req]);
                $newId = $pdo->lastInsertId();
                $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)")
                    ->execute([$role['id'], $newId]);
                echo " -> Created and Added.\n";
            }
        }
    }
} else {
    echo "Role 'administrator' not found!\n";
}

echo "\nDone.\n";
