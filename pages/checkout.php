
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/db.php';

// Only logged-in customers can checkout
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$error = '';

// Fetch cart products directly from database
function getCartItems($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT
            cart.product_id,
            cart.quantity,
            products.name,
            products.price,
            products.image
        FROM cart
        INNER JOIN products
        ON cart.product_id = products.id
        WHERE cart.user_id = ?
    ");

    $stmt->execute([$user_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$cart_items = getCartItems($conn, $user_id);

if (empty($cart_items)) {
    die("Your cart is empty. Please add products before checkout.");
}

// Calculate total using database prices
$total = 0;

foreach ($cart_items as $item) {
    $total += (float)$item['price'] * (int)$item['quantity'];
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process order
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['place_order'])) {

    if (!hash_equals(
        $_SESSION['csrf_token'],
        $_POST['csrf_token'] ?? ''
    )) {
        $error = "Invalid request. Please refresh the page.";
    } else {

        $shipping_name = trim($_POST['shipping_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $postal_code = trim($_POST['postal_code'] ?? '');

        if (
            $shipping_name === '' ||
            $address === '' ||
            $city === '' ||
            $state === '' ||
            !preg_match('/^[0-9]{10}$/', $phone) ||
            !preg_match('/^[0-9]{6}$/', $postal_code)
        ) {
            $error = "Please enter valid delivery details. Use a 10-digit phone number and 6-digit PIN code.";
        } else {

            try {
                $conn->beginTransaction();

                // Re-fetch cart data before placing order
                $cart_items = getCartItems($conn, $user_id);

                if (empty($cart_items)) {
                    throw new Exception("Your cart is empty.");
                }

                // Recalculate total on server
                $total = 0;

                foreach ($cart_items as $item) {
                    if ((int)$item['quantity'] < 1) {
                        throw new Exception("Invalid product quantity.");
                    }

                    $total += (float)$item['price']
                            * (int)$item['quantity'];
                }

                // Create order
                $stmt = $conn->prepare("
                    INSERT INTO orders
                    (
                        user_id,
                        total_amount,
                        shipping_name,
                        phone,
                        address,
                        city,
                        state,
                        postal_code,
                        payment_method,
                        payment_status,
                        order_status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'COD', 'Pending', 'Pending')
                ");

                $stmt->execute([
                    $user_id,
                    $total,
                    $shipping_name,
                    $phone,
                    $address,
                    $city,
                    $state,
                    $postal_code
                ]);

                $order_id = $conn->lastInsertId();

                // Save individual products
                $item_stmt = $conn->prepare("
                    INSERT INTO order_items
                    (
                        order_id,
                        product_id,
                        product_name,
                        price,
                        quantity,
                        subtotal
                    )
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                foreach ($cart_items as $item) {

                    $price = (float)$item['price'];
                    $quantity = (int)$item['quantity'];
                    $subtotal = $price * $quantity;

                    $item_stmt->execute([
                        $order_id,
                        $item['product_id'],
                        $item['name'],
                        $price,
                        $quantity,
                        $subtotal
                    ]);
                }

                // Clear customer's cart
                $stmt = $conn->prepare("
                    DELETE FROM cart WHERE user_id = ?
                ");

                $stmt->execute([$user_id]);

                // Commit order
                $conn->commit();

                // Prevent duplicate submission on refresh
                unset($_SESSION['csrf_token']);

                header(
                    "Location: order_success.php?id=" .
                    urlencode($order_id)
                );
                exit();

            } catch (Throwable $e) {

                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }

                error_log($e->getMessage());

                $error = "Unable to place your order. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Checkout - Online Store</title>

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
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 25px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        h2 {
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            margin-top: 7px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }

        textarea {
            resize: vertical;
            min-height: 90px;
        }

        button {
            width: 100%;
            margin-top: 20px;
            padding: 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 17px;
            cursor: pointer;
        }

        button:hover {
            background: #218838;
        }

        .product {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
        }

        .total {
            font-size: 22px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }

        .error {
            color: red;
            background: #ffe5e5;
            padding: 12px;
            border-radius: 5px;
        }

        .payment {
            padding: 15px;
            background: #f0f8f0;
            border-radius: 5px;
            margin-top: 20px;
        }

        @media(max-width: 750px) {
            .container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="card">

        <h2>Delivery Information</h2>

        <?php if ($error): ?>
            <p class="error">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <form method="POST">

            <input type="hidden"
                   name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <label>Full Name</label>
            <input type="text"
                   name="shipping_name"
                   maxlength="150"
                   required>

            <label>Phone Number</label>
            <input type="tel"
                   name="phone"
                   pattern="[0-9]{10}"
                   maxlength="10"
                   placeholder="10-digit mobile number"
                   required>

            <label>Delivery Address</label>
            <textarea name="address"
                      maxlength="2000"
                      required></textarea>

            <label>City</label>
            <input type="text"
                   name="city"
                   maxlength="100"
                   required>

            <label>State</label>
            <input type="text"
                   name="state"
                   maxlength="100"
                   required>

            <label>PIN Code</label>
            <input type="text"
                   name="postal_code"
                   pattern="[0-9]{6}"
                   maxlength="6"
                   placeholder="6-digit PIN code"
                   required>

            <div class="payment">
                <strong>Payment Method</strong>
                <p>Cash on Delivery (COD)</p>
                <small>Pay when your order arrives.</small>
            </div>

            <button type="submit" name="place_order">
                Place Order
            </button>

        </form>

    </div>

    <div class="card">

        <h2>Order Summary</h2>

        <?php foreach ($cart_items as $item): ?>

            <div class="product">

                <div>
                    <strong>
                        <?= htmlspecialchars($item['name']) ?>
                    </strong>

                    <p>
                        ₹<?= number_format((float)$item['price'], 2) ?>
                        × <?= (int)$item['quantity'] ?>
                    </p>
                </div>

                <strong>
                    ₹<?= number_format(
                        (float)$item['price'] * (int)$item['quantity'],
                        2
                    ) ?>
                </strong>

            </div>

        <?php endforeach; ?>

        <div class="total">
            Total: ₹<?= number_format($total, 2) ?>
        </div>

        <p>
            <a href="cart.php">← Back to Cart</a>
        </p>

    </div>

</div>

</body>
</html>
