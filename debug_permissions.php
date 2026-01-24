<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=sikerja;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "--- Debugging Permissions ---\n";

// 1. Get Administrator Role ID
$role = $pdo->query("SELECT id, name FROM roles WHERE name = 'administrator'")->fetch(PDO::FETCH_ASSOC);
if (!$role) {
    die("Role 'administrator' not found!\n");
}
echo "Role: {$role['name']} (ID: {$role['id']})\n";

// Check permissions table structure
$cols = $pdo->query("SHOW COLUMNS FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
echo "Permissions columns: " . implode(', ', $cols) . "\n";

$hasGuard = in_array('guard_name', $cols);
$hasSlug = in_array('slug', $cols);

// 2. Check Required Permissions
$required = [
    'master.faq.index',
    'master.faq.create',
    'master.faq.edit',
    'master.faq.destroy',
    'master.slider.index',
    'master.slider.create',
    'master.slider.edit',
    'master.slider.destroy',
    'master.laman.index',
    'master.laman.create',
    'master.laman.edit',
    'master.laman.destroy',
    'master.kategori.index',
    'master.kategori.create',
    'master.kategori.edit',
    'master.kategori.destroy',
];

echo "\nChecking Permissions in Database:\n";
foreach ($required as $req) {
    $sql = "SELECT id FROM permissions WHERE name = ?";
    $perm = $pdo->prepare($sql);
    $perm->execute([$req]);
    $p = $perm->fetch(PDO::FETCH_ASSOC);

    if ($p) {
        echo "[OK] $req (ID: {$p['id']})\n";

        // Check if assigned to role
        $assigned = $pdo->prepare("SELECT * FROM role_permissions WHERE role_id = ? AND permission_id = ?");
        $assigned->execute([$role['id'], $p['id']]);
        if ($assigned->fetch()) {
            echo "   -> Assigned: YES\n";
        } else {
            echo "   -> Assigned: NO -> Assigning...\n";
            $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)")
                ->execute([$role['id'], $p['id']]);
            echo "   -> Done.\n";
        }
    } else {
        echo "[MISSING] $req -> Creating...\n";

        // Build dynamic insert based on table structure
        $columns = ['name'];
        $values = ['?'];
        $params = [$req];

        if ($hasSlug) {
            $columns[] = 'slug';
            $values[] = '?';
            $params[] = str_replace('.', '-', $req); // Convert dots to dashes for slug
        }

        if ($hasGuard) {
            $columns[] = 'guard_name';
            $values[] = "'web'";
        }

        $columns[] = 'created_at';
        $columns[] = 'updated_at';
        $values[] = 'NOW()';
        $values[] = 'NOW()';

        $sql = "INSERT INTO permissions (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $newId = $pdo->lastInsertId();

        echo "   -> Created (ID: $newId)\n";

        // Assign to role
        $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)")
            ->execute([$role['id'], $newId]);
        echo "   -> Assigned.\n";
    }
}

// 3. Clear Cache
$cacheTable = $pdo->query("SHOW TABLES LIKE 'cache'")->fetch();
if ($cacheTable) {
    $pdo->exec("DELETE FROM cache WHERE `key` LIKE '%spatie.permission.cache%'");
    echo "\nCleared permission cache.\n";
}

echo "\nDone.\n";
