<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sundar_swadesh";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/*
------------------------------------------------------------
FETCH DATA
------------------------------------------------------------
*/

// ✅ Fetch only completed (paid) orders
$completed_sql = "SELECT * FROM Orders 
                  WHERE user_id = ? AND payment_status = 'Paid'
                  ORDER BY order_date DESC";
$completed_stmt = $conn->prepare($completed_sql);
$completed_stmt->bind_param("i", $user_id);
$completed_stmt->execute();
$completed_orders = $completed_stmt->get_result();
$completed_stmt->close();

// ✅ Fetch draft/unpaid orders (not paid or still in cart)
$draft_sql = "SELECT * FROM Orders 
              WHERE user_id = ? AND (payment_status = 'Pending' OR payment_status IS NULL)
              ORDER BY order_date DESC";
$draft_stmt = $conn->prepare($draft_sql);
$draft_stmt->bind_param("i", $user_id);
$draft_stmt->execute();
$draft_orders = $draft_stmt->get_result();
$draft_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Orders | Sundar Swadesh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #eef2ff, #e8f5e9);
            font-family: 'Poppins', sans-serif;
        }

        .navbar {
            background: linear-gradient(to right, #182848, #4b6cb7);
        }

        .navbar-brand,
        .nav-link {
            color: #fff !important;
            font-weight: 500;
        }

        .navbar-brand:hover,
        .nav-link:hover {
            color: #d1e3ff !important;
        }

        .container {
            margin-top: 70px;
            max-width: 1100px;
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 40px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(to right, #4b6cb7, #182848);
            color: white;
            padding: 15px 20px;
        }

        .card-body {
            background: #ffffff;
            padding: 25px;
        }

        .order-item {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9fbff;
        }

        .order-item:hover {
            background: #f1f4ff;
        }

        .order-status {
            font-weight: 600;
            color: #ffd700;
        }

        .order-date {
            color: #ccc;
            font-size: 0.9rem;
        }

        .btn-view {
            background-color: #4b6cb7;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            text-decoration: none;
        }

        .btn-view:hover {
            background-color: #3a58a1;
            color: white;
        }

        footer {
            text-align: center;
            padding: 15px;
            color: #555;
            margin-top: 50px;
        }

        .section-title {
            margin-top: 50px;
            color: #444;
            font-weight: bold;
        }

        .empty-msg {
            text-align: center;
            color: #999;
            font-style: italic;
        }
    </style>
</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="books.php">Sundar Swadesh</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="books.php">Books</a></li>
                    <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Your Orders</h2>

        <!-- DRAFT / UNPAID ORDERS -->
        <h4 class="section-title">Draft Orders (Pending Payment)</h4>
        <?php if ($draft_orders->num_rows > 0): ?>
            <?php while ($order = $draft_orders->fetch_assoc()): ?>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Order ID: <?php echo htmlspecialchars($order['order_id']); ?></h5>
                            <small class="order-date"><?php echo htmlspecialchars($order['order_date']); ?></small>
                        </div>
                        <span class="order-status text-warning">
                            <?php echo ucfirst(htmlspecialchars($order['payment_status'] ?? 'Pending')); ?>
                        </span>
                    </div>

                    <div class="card-body">
                        <?php
                        $order_items_sql = "SELECT oi.*, b.title, b.price, b.cover_image 
                                            FROM Order_Items oi 
                                            JOIN Books b ON oi.book_id = b.book_id 
                                            WHERE oi.order_id = ?";
                        $order_items_stmt = $conn->prepare($order_items_sql);
                        $order_items_stmt->bind_param("i", $order['order_id']);
                        $order_items_stmt->execute();
                        $order_items_result = $order_items_stmt->get_result();
                        $order_items_stmt->close();

                        if ($order_items_result->num_rows > 0):
                            while ($item = $order_items_result->fetch_assoc()):
                        ?>
                                <div class="order-item d-flex align-items-center">
                                    <?php if (!empty($item['cover_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['cover_image']); ?>" alt="Book"
                                            class="me-3"
                                            style="width:70px; height:90px; border-radius:8px; object-fit:cover;">
                                    <?php endif; ?>
                                    <div>
                                        <p class="mb-1"><strong><?php echo htmlspecialchars($item['title']); ?></strong></p>
                                        <p class="mb-1">Qty: <?php echo htmlspecialchars($item['quantity']); ?></p>
                                        <p class="mb-0">Price: $<?php echo htmlspecialchars($item['price']); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No items found for this order.</p>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0"><strong>Total:</strong> $<?php echo htmlspecialchars($order['total_price']); ?></p>
                            <a href="order_items.php?order_id=<?php echo $order['order_id']; ?>" class="btn-view">Check Items</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg">No draft orders found.</p>
        <?php endif; ?>


        <!-- COMPLETED / PAID ORDERS -->
        <h4 class="section-title mt-5">Completed Orders (Paid)</h4>
        <?php if ($completed_orders->num_rows > 0): ?>
            <?php while ($order = $completed_orders->fetch_assoc()): ?>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Order ID: <?php echo htmlspecialchars($order['order_id']); ?></h5>
                            <small class="order-date"><?php echo htmlspecialchars($order['order_date']); ?></small>
                        </div>
                        <span class="order-status text-success">
                            <?php echo ucfirst(htmlspecialchars($order['payment_status'])); ?>
                        </span>
                    </div>

                    <div class="card-body">
                        <?php
                        $order_items_sql = "SELECT oi.*, b.title, b.price, b.cover_image 
                                            FROM Order_Items oi 
                                            JOIN Books b ON oi.book_id = b.book_id 
                                            WHERE oi.order_id = ?";
                        $order_items_stmt = $conn->prepare($order_items_sql);
                        $order_items_stmt->bind_param("i", $order['order_id']);
                        $order_items_stmt->execute();
                        $order_items_result = $order_items_stmt->get_result();
                        $order_items_stmt->close();

                        if ($order_items_result->num_rows > 0):
                            while ($item = $order_items_result->fetch_assoc()):
                        ?>
                                <div class="order-item d-flex align-items-center">
                                    <?php if (!empty($item['cover_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['cover_image']); ?>" alt="Book"
                                            class="me-3"
                                            style="width:70px; height:90px; border-radius:8px; object-fit:cover;">
                                    <?php endif; ?>
                                    <div>
                                        <p class="mb-1"><strong><?php echo htmlspecialchars($item['title']); ?></strong></p>
                                        <p class="mb-1">Qty: <?php echo htmlspecialchars($item['quantity']); ?></p>
                                        <p class="mb-0">Price: $<?php echo htmlspecialchars($item['price']); ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No items found for this order.</p>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0"><strong>Total:</strong> $<?php echo htmlspecialchars($order['total_price']); ?></p>
                            <a href="order_items.php?order_id=<?php echo $order['order_id']; ?>" class="btn-view">Check Items</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg">No completed (paid) orders found.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>© 2025 Sundar Swadesh | All Rights Reserved</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>