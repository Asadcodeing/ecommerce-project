
<?php
require_once '../includes/admin_guard.php';

$message = '';
$error = '';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Create a new admin account
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['create_admin'])) {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Refresh the page.";
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Enter a valid email address.";
        } elseif (strlen($password) < 10) {
            $error = "Password must contain at least 10 characters.";
        } else {
            $stmt = $conn->prepare(
                "SELECT id FROM users WHERE email = ?"
            );
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $error = "This email is already registered. Use Promote User below.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare(
                    "INSERT INTO users (email, password, role)
                     VALUES (?, ?, 'admin')"
                );

                $stmt->execute([$email, $hash]);
                $message = "New admin account created successfully!";
            }
        }
    }
}

// Promote an existing user
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['promote_user'])) {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Refresh the page.";
    } else {
        $email = trim($_POST['promote_email'] ?? '');

        $stmt = $conn->prepare(
            "SELECT id, role FROM users WHERE email = ?"
        );
        $stmt->execute([$email]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$target) {
            $error = "No account found with that email.";
        } elseif ($target['role'] === 'admin') {
            $error = "This account is already an admin.";
        } else {
            $stmt = $conn->prepare(
                "UPDATE users SET role = 'admin' WHERE id = ?"
            );
            $stmt->execute([$target['id']]);

            $message = "User successfully promoted to admin!";
        }
    }
}

// Fetch all admin accounts
$stmt = $conn->query(
    "SELECT id, email, role, created_at
     FROM users
     WHERE role = 'admin'
     ORDER BY id ASC"
);

$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Admins</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 30px;
        }
        .container {
            max-width: 750px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
        }
        input {
            display: block;
            width: 95%;
            padding: 12px;
            margin: 12px 0;
        }
        button {
            padding: 12px 20px;
            background: #28a745;
            color: white;
            border: 0;
            cursor: pointer;
        }
        .message { color: green; }
        .error { color: red; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .back-dashboard {
    display: inline-block;
    background-color: #2c3e50;
    color: white;
    padding: 12px 22px;
    text-decoration: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

.back-dashboard:hover {
    background-color: #1a252f;
    color: white;
}
    </style>
</head>
<body>

<div class="container">

    <h2>Manage Admin Accounts</h2>

    <?php if ($message): ?>
        <p class="message">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="error">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <h3>Create New Admin</h3>

    <form method="POST">
        <input type="hidden" name="csrf_token"
               value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password (minimum 10 characters):</label>
        <input type="password" name="password"
               minlength="10" required>

        <button type="submit" name="create_admin">
            Create Admin
        </button>
    </form>

    <hr>

    <h3>Promote Existing User</h3>

    <form method="POST">
        <input type="hidden" name="csrf_token"
               value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <label>Registered User Email:</label>
        <input type="email" name="promote_email" required>

        <button type="submit" name="promote_user">
            Promote to Admin
        </button>
    </form>

    <hr>

    <h3>Existing Admin Accounts</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Role</th>
        </tr>

        <?php foreach ($admins as $admin): ?>
        <tr>
            <td><?= (int)$admin['id'] ?></td>
            <td><?= htmlspecialchars($admin['email']) ?></td>
            <td><?= htmlspecialchars($admin['role']) ?></td>
        </tr>
        <?php endforeach; ?>

    </table>

    <a href="dashboard.php" class="back-dashboard">
    ← Back to Dashboard
</a>

</div>

</body>
</html>
