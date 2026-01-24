<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=sikerja;charset=utf8mb4', 'root', '');
$tables = ['faq', 'faqs', 'slider', 'sliders', 'laman', 'permissions'];

foreach ($tables as $table) {
    echo "TABLE: $table\n";
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            echo " - {$col['Field']} ({$col['Type']})\n";
        }
    } catch (Exception $e) {
        echo " - Table not found or error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
