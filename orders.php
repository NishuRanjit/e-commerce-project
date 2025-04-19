<?php
session_start();

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sundar_swadesh";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's orders
$orders_sql = "SELECT * FROM Orders WHERE user_id = ? ORDER BY order_date DESC";
$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .container { max-width: 1200px; }
        .card { border: none; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .card-header { background-color: #007bff; color: white; }
        .card-body { padding: 20px; }
        .order-item { margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .order-status { font-weight: bold; }
        .order-date { font-size: 0.9rem; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Order History</h2>

        <!-- Display Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        <?php endif; ?>

        <!-- Orders List -->
        <?php if ($orders_result->num_rows > 0): ?>
            <?php while ($order = $orders_result->fetch_assoc()): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Order ID: <?php echo htmlspecialchars($order['order_id']); ?></h5>
                        <p class="order-status">Status: <?php echo htmlspecialchars($order['order_status']); ?></p>
                        <p class="order-date">Order Date: <?php echo htmlspecialchars($order['order_date']); ?></p>
                    </div>
                    <div class="card-body">
                        <h6>Order Items:</h6>
                        <?php
                        // Fetch order items for this order
                        $order_items_sql = "SELECT Order_Items.*, Books.title, Books.price FROM Order_Items 
                                            JOIN Books ON Order_Items.book_id = Books.book_id 
                                            WHERE order_id = ?";
                        $order_items_stmt = $conn->prepare($order_items_sql);
                        $order_items_stmt->bind_param("i", $order['order_id']);
                        $order_items_stmt->execute();
                        $order_items_result = $order_items_stmt->get_result();
                        $order_items_stmt->close();

                        if ($order_items_result->num_rows > 0):
                            while ($item = $order_items_result->fetch_assoc()):
                        ?>
                                <div class="order-item">
                                    <p><strong>Book:</strong> <?php echo htmlspecialchars($item['title']); ?></p>
                                    <p><strong>Quantity:</strong> <?php echo htmlspecialchars($item['quantity']); ?></p>
                                    <p><strong>Price:</strong> $<?php echo htmlspecialchars($item['price']); ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No items found for this order.</p>
                        <?php endif; ?>
                        <p class="mt-3"><strong>Total Price:</strong> $<?php echo htmlspecialchars($order['total_price']); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>