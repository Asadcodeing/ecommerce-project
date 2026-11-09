
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

// Check whether an admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin/login.php");
    exit();
}

// Verify the admin role directly from the database
$stmt = $conn->prepare(
    "SELECT id, role FROM users WHERE id = ?"
);
$stmt->execute([$_SESSION['admin_id']]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin || $admin['role'] !== 'admin') {
    unset($_SESSION['admin_id']);
    header("Location: ../admin/login.php");
    exit();
}
?>
