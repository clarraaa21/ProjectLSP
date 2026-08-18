<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    log_activity($pdo, $_SESSION['user_id'], 'Logout dari sistem');
}
session_unset();
session_destroy();
session_start();
set_toast('info', 'Kamu telah keluar. Sampai jumpa lagi!');
redirect('/login.php');
