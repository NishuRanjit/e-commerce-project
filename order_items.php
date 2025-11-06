<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "sundar_swadesh");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$cart_sql = "SELECT Cart.*, Books.title, Books.price 
             FROM Cart 
             JOIN Books ON Cart.book_id = Books.book_id 
             WHERE Cart.user_id = ?";
$cart_stmt = $conn->prepare($cart_sql);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$cart_stmt->close();

// Calculate total
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
    <title>🧾 Review & Confirm Your Order | Sundar Swadesh</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(120deg, #f4f9ff, #eefaf1);
            font-family: 'Poppins', sans-serif;
            color: #333;
            padding-top: 80px;
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(90deg, #182848, #4b6cb7);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .navbar-brand {
            font-weight: 600;
            color: #fff !important;
        }

        .nav-link {
            color: #fff !important;
            font-weight: 500;
        }

        .nav-link:hover {
            color: #d1e3ff !important;
        }

        .btn-outline-light {
            border-radius: 6px;
        }

        /* Container */
        .container {
            max-width: 900px;
        }

        .card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(12px);
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(90deg, #4b6cb7, #182848);
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            padding: 15px 20px;
        }

        .card-body {
            padding: 25px;
        }

        .order-item {
            background: #ffffff;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .order-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(75, 108, 183, 0.2);
        }

        h2 {
            text-align: center;
            color: #182848;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .btn-primary {
            background-color: #4b6cb7;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            font-size: 16px;
        }

        .btn-primary:hover {
            background-color: #3a58a1;
        }

        footer {
            text-align: center;
            margin-top: 60px;
            color: #777;
            font-size: 14px;
        }

        .total-section {
            background-color: #f9fbff;
            padding: 15px 20px;
            border-radius: 10px;
            border: 1px solid #e3e8ff;
        }

        .total-section h5 {
            margin: 0;
            color: #182848;
        }

        @media (max-width: 768px) {
            .order-item {
                text-align: center;
            }

            .order-item h5 {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="books.php"> Sundar Swadesh</a>
            <div class="d-flex align-items-center">
                <a href="books.php" class="nav-link me-3"><i class="fas fa-book"></i> Books</a>
                <a href="cart.php" class="nav-link me-3"><i class="fas fa-shopping-cart"></i> Cart</a>
                <a href="logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h2>🧾 Review & Confirm Your Order</h2>

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-receipt"></i> Order Summary
            </div>
            <div class="card-body">
                <?php if (!empty($cart_items)): ?>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($item['title']); ?></h5>
                                    <p class="mb-0 text-muted">Quantity: <?= $item['quantity']; ?></p>
                                </div>
                                <div class="text-end">
                                    <p class="fw-semibold mb-0">$<?= number_format($item['price'], 2); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="total-section mt-4 d-flex justify-content-between align-items-center flex-wrap">
                        <h5>Total Amount:</h5>
                        <h5><strong>$<?= number_format($total_price, 2); ?></strong></h5>
                    </div>

                    <form method="POST" action="payment.php" class="text-center mt-4">
                        <button type="submit" name="place_order" class="btn btn-primary">
                            ✅ Proceed to Secure Payment
                        </button>
                    </form>
                <?php else: ?>
                    <p class="text-muted text-center">Your cart is empty. Add some books to continue.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        © <?= date('Y'); ?> Sundar Swadesh | All Rights Reserved
    </footer>

</body>

</html>

<?php $conn->close(); ?>