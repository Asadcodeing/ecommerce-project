
<?php
require_once __DIR__ . '/../includes/admin_guard.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - Online Store</title>

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
        }

        /* Sidebar */

        .sidebar {
            width: 250px;
            height: 100vh;
            background: #1e293b;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding: 25px 15px;
        }

        .logo {
            font-size: 23px;
            font-weight: bold;
            padding: 10px;
            margin-bottom: 35px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #cbd5e1;
            text-decoration: none;
            padding: 14px;
            margin-bottom: 8px;
            border-radius: 7px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #334155;
            color: white;
        }

        .sidebar .logout {
            color: #fca5a5;
            margin-top: 30px;
        }

        /* Main Content */

        .main {
            margin-left: 250px;
            padding: 35px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 35px;
        }

        .topbar h1 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .topbar p {
            color: #64748b;
        }

        .admin-label {
            background: white;
            padding: 12px 18px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            font-weight: bold;
        }

        /* Welcome Section */

        .welcome {
            background: linear-gradient(120deg, #2563eb, #1d4ed8);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .welcome h2 {
            font-size: 25px;
            margin-bottom: 10px;
        }

        .welcome p {
            color: #dbeafe;
        }

        /* Section Heading */

        .section-title {
            margin-bottom: 20px;
            font-size: 21px;
        }

        /* Dashboard Cards */

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .dashboard-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.05);
            transition: 0.3s;
            border: 1px solid #e2e8f0;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.09);
        }

        .card-icon {
            width: 50px;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 10px;
            font-size: 25px;
            margin-bottom: 18px;
        }

        .blue {
            background: #dbeafe;
        }

        .green {
            background: #dcfce7;
        }

        .purple {
            background: #f3e8ff;
        }

        .orange {
            background: #ffedd5;
        }

        .dashboard-card h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .dashboard-card p {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .card-button {
            display: inline-block;
            background: #2563eb;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            transition: 0.3s;
        }

        .card-button:hover {
            background: #1d4ed8;
        }

        .green-button {
            background: #16a34a;
        }

        .green-button:hover {
            background: #15803d;
        }

        .purple-button {
            background: #9333ea;
        }

        .purple-button:hover {
            background: #7e22ce;
        }

        .orange-button {
            background: #ea580c;
        }

        .orange-button:hover {
            background: #c2410c;
        }

        /* Footer */

        footer {
            text-align: center;
            color: #64748b;
            margin-top: 45px;
            padding: 20px;
        }

        /* Responsive */

        @media(max-width: 850px) {
            .sidebar {
                width: 200px;
            }

            .main {
                margin-left: 200px;
                padding: 20px;
            }
        }

        @media(max-width: 600px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .sidebar .logo {
                margin-bottom: 15px;
            }

            .main {
                margin-left: 0;
                padding: 20px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .topbar h1 {
                font-size: 24px;
            }
        }

    </style>

</head>

<body>

    <!-- Sidebar -->

    <aside class="sidebar">

        <div class="logo">
            🛒 ONLINE STORE
        </div>

        <a href="dashboard.php" class="active">
            📊 Dashboard
        </a>

        <a href="add_product.php">
            ➕ Add Product
        </a>

        <a href="manage_products.php">
            📦 Manage Products
        </a>

        <a href="manage_orders.php">
            🛍️ Manage Orders
        </a>

        <a href="manage_admins.php">
            👥 Manage Admins
        </a>

        <a href="logout.php" class="logout">
            🚪 Logout
        </a>

    </aside>


    <!-- Main Content -->

    <main class="main">

        <div class="topbar">

            <div>
                <h1>Admin Dashboard</h1>
                <p>Manage your online store from one place.</p>
            </div>

            <div class="admin-label">
                👤 Administrator
            </div>

        </div>


        <!-- Welcome Section -->

        <section class="welcome">

            <h2>Welcome Back, Admin! 👋</h2>

            <p>
                Manage products, customer orders and administrator
                accounts using your dashboard.
            </p>

        </section>


        <!-- Management Cards -->

        <h2 class="section-title">
            Store Management
        </h2>


        <div class="dashboard-grid">


            <!-- Add Product -->

            <div class="dashboard-card">

                <div class="card-icon orange">
                    ➕
                </div>

                <h3>Add Product</h3>

                <p>
                    Add new products, prices, descriptions
                    and product images to your store.
                </p>

                <a href="add_product.php"
                   class="card-button orange-button">
                    Add New Product →
                </a>

            </div>


            <!-- Manage Products -->

            <div class="dashboard-card">

                <div class="card-icon blue">
                    📦
                </div>

                <h3>Manage Products</h3>

                <p>
                    View your products and manage your
                    existing product listings.
                </p>

                <a href="manage_products.php"
                   class="card-button">
                    View Products →
                </a>

            </div>


            <!-- Manage Orders -->

            <div class="dashboard-card">

                <div class="card-icon green">
                    🛍️
                </div>

                <h3>Manage Orders</h3>

                <p>
                    View customer purchases, update order
                    status and manage COD payments.
                </p>

                <a href="manage_orders.php"
                   class="card-button green-button">
                    View Orders →
                </a>

            </div>


            <!-- Manage Admins -->

            <div class="dashboard-card">

                <div class="card-icon purple">
                    👥
                </div>

                <h3>Manage Admins</h3>

                <p>
                    Create administrator accounts and
                    manage existing admin access.
                </p>

                <a href="manage_admins.php"
                   class="card-button purple-button">
                    Manage Admins →
                </a>

            </div>


        </div>


        <footer>
            © 2026 Online Store. All rights reserved.
        </footer>

    </main>

</body>

</html>
