
<?php

require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../includes/db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    die("Invalid product ID.");
}


// Fetch existing product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    http_response_code(404);
    die("Product not found.");
}


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )) {
        $error = "Invalid request. Refresh the page and try again.";
    } else {

        $name = trim($_POST['name'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (
            $name === '' ||
            !is_numeric($price) ||
            (float)$price < 0 ||
            !is_finite((float)$price) ||
            $description === ''
        ) {
            $error = "Please enter valid product details.";
        } else {

            $image = $product['image'];
            $new_image_path = null;

            // Process optional image upload
            if (
                isset($_FILES['image']) &&
                $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                $file = $_FILES['image'];

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $error = "Image upload failed.";
                } elseif ($file['size'] > 5 * 1024 * 1024) {
                    $error = "Image must be smaller than 5 MB.";
                } else {

                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file['tmp_name']);

                    $allowed_types = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp'
                    ];

                    if (!isset($allowed_types[$mime])) {
                        $error = "Only JPG, PNG and WEBP images are allowed.";
                    } else {

                        $upload_dir = __DIR__ . '/../images/';

                        if (!is_dir($upload_dir)) {
                            $error = "Images folder does not exist.";
                        } else {

                            $new_name = bin2hex(random_bytes(16))
                                . '.' . $allowed_types[$mime];

                            $destination = $upload_dir . $new_name;

                            if (move_uploaded_file(
                                $file['tmp_name'],
                                $destination
                            )) {
                                $image = $new_name;
                                $new_image_path = $destination;
                            } else {
                                $error = "Unable to save uploaded image.";
                            }
                        }
                    }
                }
            }


            if ($error === '') {

                try {

                    $stmt = $conn->prepare("
                        UPDATE products
                        SET name = ?,
                            price = ?,
                            description = ?,
                            image = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $name,
                        $price,
                        $description,
                        $image,
                        $id
                    ]);


                    // Remove old image only after successful update
                    if (
                        $new_image_path !== null &&
                        !empty($product['image'])
                    ) {

                        $old_image = __DIR__ . '/../images/'
                            . basename($product['image']);

                        if (
                            is_file($old_image) &&
                            realpath($old_image) !== realpath($new_image_path)
                        ) {
                            unlink($old_image);
                        }
                    }

                    header("Location: manage_products.php");
                    exit();

                } catch (PDOException $e) {

                    // Remove newly uploaded file if DB update fails
                    if ($new_image_path !== null &&
                        is_file($new_image_path)) {
                        unlink($new_image_path);
                    }

                    error_log($e->getMessage());

                    $error = "Unable to update product.";

                }
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Product</title>

<style>

* {
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f1f5f9;
    padding: 30px;
    color: #1e293b;
}

.container {
    max-width: 650px;
    margin: 30px auto;
    background: white;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.07);
}

h1 {
    text-align: center;
    margin-bottom: 25px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
}

input, textarea {
    width: 100%;
    padding: 13px;
    margin-bottom: 20px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 15px;
}

textarea {
    min-height: 110px;
    resize: vertical;
}

.current-image {
    width: 110px;
    height: 110px;
    object-fit: contain;
    border: 1px solid #e2e8f0;
    padding: 8px;
    border-radius: 8px;
    margin-bottom: 20px;
}

button, .back-button {
    display: inline-block;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    cursor: pointer;
    font-size: 15px;
}

button {
    background: #2563eb;
    color: white;
}

button:hover {
    background: #1d4ed8;
}

.back-button {
    background: #334155;
    color: white;
    margin-right: 8px;
}

.error {
    background: #fee2e2;
    color: #991b1b;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
}

</style>

</head>

<body>

<div class="container">

    <h1>✏ Edit Product</h1>

    <?php if ($error): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <form method="POST" enctype="multipart/form-data">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
        >

        <label>Product Name</label>

        <input
            type="text"
            name="name"
            value="<?= htmlspecialchars($product['name']) ?>"
            maxlength="255"
            required
        >


        <label>Price (₹)</label>

        <input
            type="number"
            name="price"
            step="0.01"
            min="0"
            value="<?= htmlspecialchars((string)$product['price']) ?>"
            required
        >


        <label>Description</label>

        <textarea
            name="description"
            required
        ><?= htmlspecialchars($product['description']) ?></textarea>


        <label>Current Product Image</label>

        <?php if (!empty($product['image'])): ?>

            <img
                src="../images/<?= rawurlencode(basename($product['image'])) ?>"
                class="current-image"
                alt="Current product image"
            >

        <?php else: ?>

            <p>No image uploaded.</p>

        <?php endif; ?>


        <label>Upload New Image (Optional)</label>

        <input
            type="file"
            name="image"
            accept="image/jpeg,image/png,image/webp"
        >

        <p style="color:#64748b; font-size:13px; margin-top:-12px; margin-bottom:20px;">
            JPG, PNG or WEBP. Maximum 5 MB.
        </p>


        <button type="submit">
            Save Changes
        </button>

        <a href="manage_products.php" class="back-button">
            Cancel
        </a>

    </form>

</div>

</body>
</html>
