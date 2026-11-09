
<?php
include 'includes/db.php';

$email = 'admin@example.com';
$new_password = 'Admin@12345';

$hashed_password = password_hash(
    $new_password,
    PASSWORD_DEFAULT
);

$stmt = $conn->prepare(
    "UPDATE users
     SET password = ?, role = 'admin'
     WHERE email = ?"
);

$stmt->execute([$hashed_password, $email]);

if ($stmt->rowCount() > 0) {
    echo "Admin password updated successfully!";
} else {
    echo "No account updated. Check the email and database.";
}
?>
