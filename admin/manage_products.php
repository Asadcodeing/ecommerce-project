
<?php

require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../includes/db.php';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch products
$stmt = $conn->query("
    SELECT id, name, price, description, image
    FROM products
    ORDER BY id DESC
");

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$message = $_SESSION['product_message'] ?? '';
unset($_SESSION['product_message']);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Products | Admin</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f1f5f9;
    color: #1e293b;
    padding: 30px;
}

.container {
    max-width: 1250px;
    margin: auto;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
}

.header h1 {
    font-size: 28px;
    margin-bottom: 7px;
}

.header p {
    color: #64748b;
}

.button {
    display: inline-block;
    text-decoration: none;
    padding: 11px 18px;
    border-radius: 6px;
    font-weight: bold;
    font-size: 14px;
    border: none;
    cursor: pointer;
}

.add-button {
    background: #16a34a;
    color: white;
}

.add-button:hover {
    background: #15803d;
}

.back-button {
    background: #334155;
    color: white;
    margin-bottom: 20px;
}

.back-button:hover {
    background: #1e293b;
}

.summary {
    background: white;
    padding: 22px;
    border-radius: 10px;
    margin-bottom: 25px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    border-left: 5px solid #2563eb;
}

.summary h3 {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 8px;
}

.summary p {
    font-size: 28px;
    font-weight: bold;
}

.table-container {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.05);
    overflow-x: auto;
}

.table-heading {
    font-size: 20px;
    margin-bottom: 20px;
}

.search-box {
    width: 100%;
    max-width: 350px;
    padding: 12px;
    margin-bottom: 20px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 750px;
}

th {
    background: #1e293b;
    color: white;
    padding: 16px 12px;
    text-align: left;
    font-size: 14px;
}

td {
    padding: 15px 12px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
    font-size: 14px;
}

tbody tr:hover {
    background: #f8fafc;
}

.product-image {
    width: 65px;
    height: 65px;
    object-fit: contain;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding: 5px;
}

.product-name {
    font-weight: bold;
}

.product-description {
    max-width: 250px;
    color: #64748b;
}

.price {
    color: #15803d;
    font-weight: bold;
    white-space: nowrap;
}

.action-buttons {
    display: flex;
    gap: 8px;
    align-items: center;
}

.edit-button {
    background: #2563eb;
    color: white;
}

.edit-button:hover {
    background: #1d4ed8;
}

.delete-button {
    background: #dc2626;
    color: white;
}

.delete-button:hover {
    background: #b91c1c;
}

.empty {
    text-align: center;
    padding: 40px;
    color: #64748b;
}

.empty h3 {
    margin-bottom: 10px;
    color: #334155;
}

.delete-form {
    display: inline;
}

@media(max-width: 600px) {

    body {
        padding: 15px;
    }

    .header h1 {
        font-size: 23px;
    }

    .table-container {
        padding: 15px;
    }

}

</style>

</head>

<body>

<div class="container">

    <!-- Header -->

    <div class="header">

        <div>
            <h1>📦 Manage Products</h1>
            <p>View, edit and manage your store products.</p>
        </div>

        <a href="add_product.php" class="button add-button">
            + Add New Product
        </a>

    </div>

    <!-- Success Message -->

    <?php if (!empty($message)): ?>

        <div style="
            background: #dcfce7;
            color: #166534;
            padding: 15px;
            border-radius: 7px;
            margin-bottom: 20px;
            font-weight: bold;
        ">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <!-- Back Button -->

    <a href="dashboard.php" class="button back-button">
        ← Back to Dashboard
    </a>


    <!-- Product Count -->

    <div class="summary">

        <h3>Total Products</h3>

        <p><?= count($products) ?></p>

    </div>


    <!-- Product Table -->

    <div class="table-container">

        <h2 class="table-heading">All Products</h2>

        <input
            type="text"
            id="searchInput"
            class="search-box"
            placeholder="🔍 Search products..."
            onkeyup="searchProducts()"
        >

        <?php if (!empty($products)): ?>

        <table id="productTable">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>

            <?php foreach ($products as $product): ?>

                <tr>

                    <td>
                        #<?= (int)$product['id'] ?>
                    </td>

                    <td class="product-name">
                        <?= htmlspecialchars($product['name']) ?>
                    </td>

                    <td class="price">
                        ₹<?= number_format((float)$product['price'], 2) ?>
                    </td>

                    <td class="product-description">
                        <?= htmlspecialchars($product['description']) ?>
                    </td>

                    <td>

                        <?php if (!empty($product['image'])): ?>

                            <img
                                src="../images/<?= rawurlencode(basename($product['image'])) ?>"
                                class="product-image"
                                alt="<?= htmlspecialchars($product['name']) ?>"
                            >

                        <?php else: ?>

                            <span>No Image</span>

                        <?php endif; ?>

                    </td>

                    <td>

                        <div class="action-buttons">

                            <a
                                href="edit_product.php?id=<?= (int)$product['id'] ?>"
                                class="button edit-button"
                            >
                                ✏ Edit
                            </a>


                            <form
                                action="delete_product.php"
                                method="POST"
                                class="delete-form"
                                onsubmit="return confirm('Are you sure you want to permanently delete this product?');"
                            >

                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= (int)$product['id'] ?>"
                                >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
                                >

                                <button
                                    type="submit"
                                    class="button delete-button"
                                >
                                    🗑 Delete
                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

        <?php else: ?>

            <div class="empty">

                <h3>No Products Found</h3>

                <p>Add your first product to display it here.</p>

                <br>

                <a href="add_product.php" class="button add-button">
                    Add Product
                </a>

            </div>

        <?php endif; ?>

    </div>

</div>


<script>

function searchProducts() {

    let input = document.getElementById("searchInput");

    let filter = input.value.toLowerCase();

    let table = document.getElementById("productTable");

    if (!table) return;

    let rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) {

        let text = rows[i].textContent.toLowerCase();

        rows[i].style.display =
            text.includes(filter) ? "" : "none";

    }

}

</script>

</body>
</html>
