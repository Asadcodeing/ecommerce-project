
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$order_id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$order_id || $order_id < 1) {
    http_response_code(400);
    die("Invalid order ID.");
}


// Fetch order belonging to logged-in customer
$stmt = $conn->prepare("
    SELECT *
    FROM orders
    WHERE id = ? AND user_id = ?
");

$stmt->execute([$order_id, $user_id]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);


// Prevent access to another customer's order
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


// Order tracking stages
$statuses = [
    'Pending',
    'Processing',
    'Shipped',
    'Delivered'
];

$current_status = $order['order_status'];

$current_index = array_search(
    $current_status,
    $statuses,
    true
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Order Details</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f7fa;
            margin: 0;
            padding: 30px;
            color: #333;
        }

        .container {
            max-width: 950px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        h1 {
            text-align: center;
        }

        .order-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .order-box p {
            line-height: 1.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 13px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #2c3e50;
            color: white;
        }

        .total {
            text-align: right;
            font-size: 23px;
            font-weight: bold;
            margin-top: 25px;
        }

        .tracking {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin: 35px 0;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
            color: #999;
        }

        .circle {
            width: 35px;
            height: 35px;
            line-height: 35px;
            border-radius: 50%;
            background: #ddd;
            color: #555;
            margin: 0 auto 10px;
            font-weight: bold;
        }

        .step.completed {
            color: #28a745;
        }

        .step.completed .circle {
            background: #28a745;
            color: white;
        }

        .step.current {
            color: #007bff;
            font-weight: bold;
        }

        .step.current .circle {
            background: #007bff;
            color: white;
        }

        .cancelled {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
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

        @media(max-width: 600px) {

            body {
                padding: 12px;
            }

            .container {
                padding: 18px;
                overflow-x: auto;
            }

            .tracking {
                gap: 3px;
            }

            .step {
                font-size: 11px;
            }

            .circle {
                width: 28px;
                height: 28px;
                line-height: 28px;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <h1>Order Details</h1>


    <div class="order-box">

        <h2>
            Order #<?= (int)$order['id'] ?>
        </h2>

        <p>
            <strong>Order Date:</strong>
            <?= htmlspecialchars($order['created_at']) ?>
        </p>

        <p>
            <strong>Order Status:</strong>
            <?= htmlspecialchars($current_status) ?>
        </p>

        <p>
            <strong>Payment Method:</strong>
            <?= htmlspecialchars($order['payment_method']) ?>
        </p>

        <p>
            <strong>Payment Status:</strong>
            <?= htmlspecialchars($order['payment_status']) ?>
        </p>

    </div>


    <!-- Order Tracking -->

    <h2>Track Your Order</h2>


    <?php if ($current_status === 'Cancelled'): ?>

        <div class="cancelled">
            Your order has been cancelled.
        </div>

    <?php else: ?>

        <div class="tracking">

            <?php foreach ($statuses as $index => $status): ?>

                <?php

                $class = '';

                if ($current_index !== false) {

                    if ($index < $current_index) {
                        $class = 'completed';
                    }

                    if ($index === $current_index) {
                        $class = 'current';
                    }

                }

                ?>

                <div class="step <?= $class ?>">

                    <div class="circle">

                        <?= $index + 1 ?>

                    </div>

                    <span>
                        <?= htmlspecialchars($status) ?>
                    </span>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>


    <!-- Delivery Information -->

    <div class="order-box">

        <h2>Delivery Information</h2>

        <p>
            <strong>Name:</strong>
            <?= htmlspecialchars($order['shipping_name']) ?>
        </p>

        <p>
            <strong>Phone:</strong>
            <?= htmlspecialchars($order['phone']) ?>
        </p>

        <p>
            <strong>Address:</strong><br>

            <?= nl2br(htmlspecialchars($order['address'])) ?><br>

            <?= htmlspecialchars($order['city']) ?>,

            <?= htmlspecialchars($order['state']) ?> -

            <?= htmlspecialchars($order['postal_code']) ?>

        </p>

    </div>


    <!-- Products -->

    <h2>Ordered Products</h2>

    <table>

        <thead>

            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>

        </thead>

        <tbody>

            <?php foreach ($items as $item): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($item['product_name']) ?>
                    </td>

                    <td>
                        ₹<?= number_format(
                            (float)$item['price'],
                            2
                        ) ?>
                    </td>

                    <td>
                        <?= (int)$item['quantity'] ?>
                    </td>

                    <td>
                        ₹<?= number_format(
                            (float)$item['subtotal'],
                            2
                        ) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>


    <div class="total">

        Total:

        ₹<?= number_format(
            (float)$order['total_amount'],
            2
        ) ?>

    </div>


    <button type="button" onclick="history.back()" class="back-button">
    ← Go Back
</button>

</div>

</body>

</html>
