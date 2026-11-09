
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$order_id || $order_id < 1) {
    die("Invalid order.");
}

// Fetch only the logged-in user's order
$stmt = $conn->prepare("
    SELECT *
    FROM orders
    WHERE id = ? AND user_id = ?
");

$stmt->execute([$order_id, $user_id]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    die("Order not found.");
}

// Fetch ordered products
$stmt = $conn->prepare("
    SELECT *
    FROM order_items
    WHERE order_id = ?
");

$stmt->execute([$order_id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Order Confirmed</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fa;
            padding: 30px;
            color: #333;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 35px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .success {
            text-align: center;
            color: #28a745;
        }

        .details {
            background: #f7f7f7;
            padding: 20px;
            margin-top: 25px;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .total {
            text-align: right;
            font-size: 22px;
            font-weight: bold;
        }

        .button {
            display: inline-block;
            margin-top: 25px;
            padding: 13px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="success">
        <h1>✓ Order Placed Successfully!</h1>
        <p>Thank you for shopping with us.</p>
    </div>

    <div class="details">

        <h3>Order Details</h3>

        <p>
            <strong>Order ID:</strong>
            #<?= (int)$order['id'] ?>
        </p>

        <p>
            <strong>Order Status:</strong>
            <?= htmlspecialchars($order['order_status']) ?>
        </p>

        <p>
            <strong>Payment:</strong>
            Cash on Delivery
        </p>

        <p>
            <strong>Payment Status:</strong>
            <?= htmlspecialchars($order['payment_status']) ?>
        </p>

        <p>
            <strong>Delivery Address:</strong><br>
            <?= nl2br(htmlspecialchars($order['address'])) ?><br>
            <?= htmlspecialchars($order['city']) ?>,
            <?= htmlspecialchars($order['state']) ?> -
            <?= htmlspecialchars($order['postal_code']) ?>
        </p>

    </div>

    <h3>Ordered Products</h3>

    <table>

        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>

        <?php foreach ($items as $item): ?>

        <tr>
            <td><?= htmlspecialchars($item['product_name']) ?></td>

            <td>
                ₹<?= number_format((float)$item['price'], 2) ?>
            </td>

            <td><?= (int)$item['quantity'] ?></td>

            <td>
                ₹<?= number_format((float)$item['subtotal'], 2) ?>
            </td>
        </tr>

        <?php endforeach; ?>

    </table>

    <p class="total">
        Total: ₹<?= number_format((float)$order['total_amount'], 2) ?>
    </p>

    <div style="text-align:center;">
        <a href="../index.php" class="button">
            Continue Shopping
        </a>
    </div>

</div>

</body>
</html>
