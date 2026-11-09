
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';

// Check customer login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Fetch orders belonging only to this customer
$stmt = $conn->prepare("
    SELECT *
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$user_id]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Orders - Online Store</title>

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
            max-width: 1100px;
            margin: auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .order-card {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }

        .order-header h2 {
            margin: 0;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
        }

        .info-box p {
            margin: 5px 0;
        }

        .status {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            background: #fff3cd;
            color: #856404;
        }

        .status.processing {
            background: #cce5ff;
            color: #004085;
        }

        .status.shipped {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status.delivered {
            background: #d4edda;
            color: #155724;
        }

        .status.cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .button {
            display: inline-block;
            padding: 11px 18px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .button:hover {
            background: #0056b3;
        }

        .empty {
            background: white;
            padding: 50px 20px;
            text-align: center;
            border-radius: 10px;
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

        @media(max-width: 700px) {

            body {
                padding: 15px;
            }

            .order-info {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <button type="button" onclick="history.back()" class="back-button">
    ← Go Back
</button>

    <h1>My Orders</h1>


    <?php if (!empty($orders)): ?>

        <?php foreach ($orders as $order): ?>

            <?php

            $status_class = strtolower(
                $order['order_status']
            );

            ?>

            <div class="order-card">

                <div class="order-header">

                    <h2>
                        Order #<?= (int)$order['id'] ?>
                    </h2>

                    <span class="status <?= htmlspecialchars($status_class) ?>">
                        <?= htmlspecialchars($order['order_status']) ?>
                    </span>

                </div>


                <div class="order-info">

                    <div class="info-box">

                        <p><strong>Order Date</strong></p>

                        <p>
                            <?= htmlspecialchars($order['created_at']) ?>
                        </p>

                    </div>


                    <div class="info-box">

                        <p><strong>Total Amount</strong></p>

                        <p>
                            ₹<?= number_format(
                                (float)$order['total_amount'],
                                2
                            ) ?>
                        </p>

                    </div>


                    <div class="info-box">

                        <p><strong>Payment Method</strong></p>

                        <p>
                            <?= htmlspecialchars($order['payment_method']) ?>
                        </p>

                        <p>
                            Payment:
                            <?= htmlspecialchars($order['payment_status']) ?>
                        </p>

                    </div>

                </div>


                <a
                    href="order_details.php?id=<?= (int)$order['id'] ?>"
                    class="button"
                >
                    View Order Details
                </a>

            </div>

        <?php endforeach; ?>


    <?php else: ?>

        <div class="empty">

            <h2>You haven't placed any orders yet.</h2>

            <p>Explore our store and place your first order.</p>

            <a href="../index.php" class="button">
                Start Shopping
            </a>

        </div>

    <?php endif; ?>

</div>

</body>

</html>
