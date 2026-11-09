
<?php

require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../includes/db.php';


// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("Method not allowed.");
}


// Verify CSRF token
if (!hash_equals(
    $_SESSION['csrf_token'] ?? '',
    $_POST['csrf_token'] ?? ''
)) {
    http_response_code(403);
    die("Invalid request. Please go back and try again.");
}


// Validate product ID
$product_id = filter_input(
    INPUT_POST,
    'product_id',
    FILTER_VALIDATE_INT
);

if (!$product_id || $product_id < 1) {
    die("Invalid product ID.");
}


// Fetch product image
$stmt = $conn->prepare("
    SELECT image
    FROM products
    WHERE id = ?
");

$stmt->execute([$product_id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: manage_products.php");
    exit();
}


try {

    // Delete product from database
    $stmt = $conn->prepare("
        DELETE FROM products
        WHERE id = ?
    ");

    $stmt->execute([$product_id]);


    // Delete image after successful database deletion
    if (!empty($product['image'])) {

        $image_path = __DIR__ . '/../images/'
            . basename($product['image']);

        if (is_file($image_path)) {
            unlink($image_path);
        }
    }


    $_SESSION['product_message'] =
        "Product deleted successfully!";

    header("Location: manage_products.php");
    exit();

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(409);

    die(
        "This product could not be deleted. "
        . "It may be referenced by an existing order. "
        . "Check the database relationships before trying again."
    );

}

?>
