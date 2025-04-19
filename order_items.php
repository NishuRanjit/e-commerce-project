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

// Fetch user's cart items
$cart_sql = "SELECT Cart.*, Books.price FROM Cart 
             JOIN Books ON Cart.book_id = Books.book_id 
             WHERE user_id = ?";
$cart_stmt = $conn->prepare($cart_sql);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$cart_stmt->close();

// Calculate total price
$total_price = 0;
$cart_items = [];
while ($item = $cart_result->fetch_assoc()) {
    $total_price += $item['price'] * $item['quantity'];
    $cart_items[] = $item;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .container { max-width: 800px; }
        .card { border: none; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .card-header { background-color: #007bff; color: white; }
        .card-body { padding: 20px; }
        .order-item { margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">Place Order</h2>

        <!-- Display Messages -->
        <?php if (isset($_SESSION['message']) && $_SESSION['message'] !== "Incorrect password!"): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        <?php endif; ?>

        <!-- Order Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Order Summary</h5>
            </div>
            <div class="card-body">
                <?php foreach ($cart_items as $item): ?>
                    <div class="order-item">
                        <p><strong>Quantity:</strong> <?php echo htmlspecialchars($item['quantity']); ?></p>
                        <p><strong>Price:</strong> $<?php echo htmlspecialchars($item['price']); ?></p>
                    </div>
                <?php endforeach; ?>
                <p class="mt-3"><strong>Total Price:</strong> $<?php echo htmlspecialchars($total_price); ?></p>
            </div>
        </div>

        <!-- Place Order Form -->
        <form method="POST" action="payment.php">
            <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>