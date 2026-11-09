
<?php

// Admin authentication
require_once __DIR__ . '/../includes/admin_guard.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = $_SESSION['order_message'] ?? '';
unset($_SESSION['order_message']);

// Allowed statuses
$allowed_order_statuses = [
    'Pending',
    'Processing',
    'Shipped',
    'Delivered',
    'Cancelled'
];

$allowed_payment_statuses = [
    'Pending',
    'Paid'
];

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['update_order'])) {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {

        $message = "Invalid request. Please refresh the page.";

    } else {

        $order_id = filter_var(
            $_POST['order_id'] ?? '',
            FILTER_VALIDATE_INT
        );

        $order_status = $_POST['order_status'] ?? '';
        $payment_status = $_POST['payment_status'] ?? '';

        if (
            !$order_id ||
            !in_array($order_status, $allowed_order_statuses, true) ||
            !in_array($payment_status, $allowed_payment_statuses, true)
        ) {

            $message = "Invalid order details.";

        } else {

            try {

                $stmt = $conn->prepare("
                    UPDATE orders
                    SET order_status = ?,
                        payment_status = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $order_status,
                    $payment_status,
                    $order_id
                ]);

                $_SESSION['order_message'] =
                    "Order #{$order_id} updated successfully!";

                header("Location: manage_orders.php");
                exit();

            } catch (PDOException $e) {

                error_log($e->getMessage());

                $message = "Unable to update the order.";

            }
        }
    }
}


// Fetch dashboard statistics
$stats_query = $conn->query("
    SELECT
        COUNT(*) AS total_orders,

        SUM(CASE
            WHEN order_status = 'Pending' THEN 1
            ELSE 0
        END) AS pending_orders,

        SUM(CASE
            WHEN order_status = 'Processing' THEN 1
            ELSE 0
        END) AS processing_orders,

        SUM(CASE
            WHEN order_status = 'Shipped' THEN 1
            ELSE 0
        END) AS shipped_orders,

        SUM(CASE
            WHEN order_status = 'Delivered' THEN 1
            ELSE 0
        END) AS delivered_orders

    FROM orders
");

$stats = $stats_query->fetch(PDO::FETCH_ASSOC);


// Fetch all orders and customer details
$orders_query = $conn->query("
    SELECT
        orders.*,
        users.email AS customer_email

    FROM orders

    LEFT JOIN users
        ON orders.user_id = users.id

    ORDER BY orders.id DESC
");

$orders = $orders_query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Orders - Admin Dashboard</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f7fa;
            margin: 0;
            padding: 25px;
            color: #333;
        }

        .container {
            max-width: 1300px;
            margin: auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08);
        }

        .stat-card h3 {
            font-size: 15px;
            color: #777;
        }

        .stat-card p {
            font-size: 27px;
            font-weight: bold;
            margin-bottom: 0;
        }

        .order-card {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }

        .order-header h2 {
            margin: 0;
            font-size: 21px;
        }

        .details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 20px 0;
        }

        .detail-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
        }

        .detail-box h3 {
            margin-top: 0;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #2c3e50;
            color: white;
        }

        .total {
            text-align: right;
            font-size: 21px;
            font-weight: bold;
            margin-top: 15px;
        }

        .update-form {
            background: #f8f9fa;
            padding: 18px;
            margin-top: 20px;
            border-radius: 6px;
        }

        .update-form label {
            display: block;
            margin: 12px 0 6px;
            font-weight: bold;
        }

        select {
            padding: 10px;
            width: 100%;
            max-width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 20px;
            margin-top: 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #218838;
        }

        .message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .back-button {
    background-color: #2c3e50;
    color: white;
    padding: 12px 22px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 20px;
}

.back-button:hover {
    background-color: #1a252f;
}

        .empty {
            background: white;
            padding: 35px;
            text-align: center;
            border-radius: 8px;
        }

        @media(max-width: 900px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width: 600px) {
            body {
                padding: 12px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .details {
                grid-template-columns: 1fr;
            }

            .order-card {
                padding: 15px;
                overflow-x: auto;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <button type="button" onclick="history.back()" class="back-button">
    ← Go Back
</button>

    <h1>Admin Order Management</h1>


    <?php if ($message): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <!-- Order Statistics -->

    <div class="stats">

        <div class="stat-card">
            <h3>Total Orders</h3>
            <p><?= (int)$stats['total_orders'] ?></p>
        </div>

        <div class="stat-card">
            <h3>Pending</h3>
            <p><?= (int)$stats['pending_orders'] ?></p>
        </div>

        <div class="stat-card">
            <h3>Processing</h3>
            <p><?= (int)$stats['processing_orders'] ?></p>
        </div>

        <div class="stat-card">
            <h3>Shipped</h3>
            <p><?= (int)$stats['shipped_orders'] ?></p>
        </div>

        <div class="stat-card">
            <h3>Delivered</h3>
            <p><?= (int)$stats['delivered_orders'] ?></p>
        </div>

    </div>


    <!-- All Customer Orders -->

    <h2>All Customer Orders</h2>


    <?php if (!empty($orders)): ?>

        <?php foreach ($orders as $order): ?>

            <div class="order-card">

                <div class="order-header">

                    <h2>
                        Order #<?= (int)$order['id'] ?>
                    </h2>

                    <span>
                        <?= htmlspecialchars($order['created_at']) ?>
                    </span>

                </div>


                <!-- Customer and Delivery Details -->

                <div class="details">

                    <div class="detail-box">

                        <h3>Customer Details</h3>

                        <p>
                            <strong>Name:</strong>
                            <?= htmlspecialchars($order['shipping_name']) ?>
                        </p>

                        <p>
                            <strong>Email:</strong>
                            <?= htmlspecialchars($order['customer_email'] ?? 'Not available') ?>
                        </p>

                        <p>
                            <strong>Phone:</strong>
                            <?= htmlspecialchars($order['phone']) ?>
                        </p>

                    </div>


                    <div class="detail-box">

                        <h3>Delivery Address</h3>

                        <p>
                            <?= nl2br(htmlspecialchars($order['address'])) ?>
                        </p>

                        <p>
                            <?= htmlspecialchars($order['city']) ?>,
                            <?= htmlspecialchars($order['state']) ?>
                        </p>

                        <p>
                            <strong>PIN:</strong>
                            <?= htmlspecialchars($order['postal_code']) ?>
                        </p>

                    </div>

                </div>


                <!-- Ordered Products -->

                <h3>Ordered Products</h3>

                <table>

                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>


                    <?php

                    $item_stmt = $conn->prepare("
                        SELECT *
                        FROM order_items
                        WHERE order_id = ?
                    ");

                    $item_stmt->execute([$order['id']]);

                    $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);

                    ?>


                    <?php foreach ($items as $item): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($item['product_name']) ?>
                            </td>

                            <td>
                                ₹<?= number_format((float)$item['price'], 2) ?>
                            </td>

                            <td>
                                <?= (int)$item['quantity'] ?>
                            </td>

                            <td>
                                ₹<?= number_format((float)$item['subtotal'], 2) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </table>


                <div class="total">

                    Order Total:
                    ₹<?= number_format((float)$order['total_amount'], 2) ?>

                </div>


                <!-- Payment Information -->

                <div class="details">

                    <div class="detail-box">

                        <h3>Payment Information</h3>

                        <p>
                            <strong>Method:</strong>
                            <?= htmlspecialchars($order['payment_method']) ?>
                        </p>

                        <p>
                            <strong>Payment Status:</strong>
                            <?= htmlspecialchars($order['payment_status']) ?>
                        </p>

                    </div>

                </div>


                <!-- Update Order and Payment Status -->

                <div class="update-form">

                    <h3>Update Order</h3>

                    <form method="POST">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
                        >

                        <input
                            type="hidden"
                            name="order_id"
                            value="<?= (int)$order['id'] ?>"
                        >

                        <label>Order Status</label>

                        <select name="order_status" required>

                            <?php foreach ($allowed_order_statuses as $status): ?>

                                <option
                                    value="<?= htmlspecialchars($status) ?>"
                                    <?= $order['order_status'] === $status ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($status) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>


                        <label>Payment Status</label>

                        <select name="payment_status" required>

                            <?php foreach ($allowed_payment_statuses as $status): ?>

                                <option
                                    value="<?= htmlspecialchars($status) ?>"
                                    <?= $order['payment_status'] === $status ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($status) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>


                        <br>

                        <button type="submit" name="update_order">
                            Update Order
                        </button>

                    </form>

                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="empty">
            <h2>No Orders Found</h2>
            <p>Customer orders will appear here after they complete checkout.</p>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
