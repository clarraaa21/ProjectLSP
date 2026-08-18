<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu.']);
    exit;
}

$userId   = $_SESSION['user_id'];
$gunungId = (int)($_POST['gunung_id'] ?? 0);

if ($gunungId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak valid.']);
    exit;
}

$cek = $pdo->prepare("SELECT id FROM wishlist WHERE user_id=? AND gunung_id=?");
$cek->execute([$userId, $gunungId]);
$existing = $cek->fetch();

if ($existing) {
    $pdo->prepare("DELETE FROM wishlist WHERE id=?")->execute([$existing['id']]);
    echo json_encode(['status' => 'ok', 'action' => 'removed']);
} else {
    $pdo->prepare("INSERT INTO wishlist (user_id, gunung_id) VALUES (?, ?)")->execute([$userId, $gunungId]);
    echo json_encode(['status' => 'ok', 'action' => 'added']);
}
