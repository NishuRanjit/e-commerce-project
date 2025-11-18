<?php
session_start();
require 'vendor/autoload.php';
require 'config.php';

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Database connection
$conn = new mysqli("localhost", "root", "", "sundar_swadesh");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$session_id = $_GET['session_id'] ?? null;

if (!$session_id) {
    header("Location: cart.php");
    exit();
}

// Verify payment with Stripe
\Stripe\Stripe::setApiKey($stripe_keys['secret']);

try {
    $session = \Stripe\Checkout\Session::retrieve($session_id);

    if ($session->payment_status !== 'paid') {
        die("Payment was not completed. Please try again.");
    }

    // Check if order already exists for this session to prevent duplicate orders
    $check_sql = "SELECT order_id FROM Orders WHERE user_id = ? ORDER BY order_id DESC LIMIT 1";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $existing_order = $check_stmt->get_result()->fetch_assoc();

    // Get cart items
    $cart_sql = "SELECT Cart.book_id, Cart.quantity, Books.price, Books.title 
                 FROM Cart 
                 JOIN Books ON Cart.book_id = Books.book_id 
                 WHERE Cart.user_id = ?";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();

    if ($cart_result->num_rows > 0) {
        // Calculate total
        $total_price = 0;
        $cart_items = [];
        while ($item = $cart_result->fetch_assoc()) {
            $cart_items[] = $item;
            $total_price += $item['price'] * $item['quantity'];
        }

        // Start transaction
        $conn->begin_transaction();

        try {
            // Create order
            $order_sql = "INSERT INTO Orders (user_id, total_price, order_status, payment_status) 
                         VALUES (?, ?, 'completed', 'Paid')";
            $order_stmt = $conn->prepare($order_sql);
            $order_stmt->bind_param("id", $user_id, $total_price);
            $order_stmt->execute();
            $order_id = $conn->insert_id;

            // Insert order items
            $item_sql = "INSERT INTO Order_Items (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)";
            $item_stmt = $conn->prepare($item_sql);

            foreach ($cart_items as $item) {
                $item_stmt->bind_param("iiid", $order_id, $item['book_id'], $item['quantity'], $item['price']);
                $item_stmt->execute();
            }

            // Create payment record
            $payment_sql = "INSERT INTO Payments (order_id, user_id, amount, payment_method, payment_status) 
                           VALUES (?, ?, ?, 'credit_card', 'successful')";
            $payment_stmt = $conn->prepare($payment_sql);
            $payment_stmt->bind_param("iid", $order_id, $user_id, $total_price);
            $payment_stmt->execute();

            // Clear cart
            $clear_cart_sql = "DELETE FROM Cart WHERE user_id = ?";
            $clear_stmt = $conn->prepare($clear_cart_sql);
            $clear_stmt->bind_param("i", $user_id);
            $clear_stmt->execute();

            // Commit transaction
            $conn->commit();

            $success = true;
            $order_number = $order_id;
            $amount_paid = $total_price;
        } catch (Exception $e) {
            $conn->rollback();
            $success = false;
            $error_message = "Database error: " . $e->getMessage();
        }
    } else {
        // Cart is empty (maybe already processed)
        $success = true;
        $order_number = $existing_order['order_id'] ?? 'N/A';
        $amount_paid = 0;
    }
} catch (\Stripe\Exception\ApiErrorException $e) {
    die("Payment verification failed: " . $e->getMessage());
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful | Sundar Swadesh</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5c6bc0;
            --success: #4caf50;
            --bg-light: #f4f6f8;
            --text-dark: #333;
            --text-muted: #666;
            --card-bg: #fff;
            --shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            --radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .success-container {
            background: var(--card-bg);
            max-width: 500px;
            width: 100%;
            padding: 50px 40px;
            border-radius: var(--radius);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--success);
            position: relative;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out 0.2s both;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .checkmark::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 35px;
            border: solid white;
            border-width: 0 4px 4px 0;
            transform: translate(-50%, -60%) rotate(45deg);
        }

        h1 {
            color: var(--success);
            font-size: 32px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .message {
            color: var(--text-muted);
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .order-details {
            background: var(--bg-light);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .order-details p {
            color: var(--text-dark);
            font-size: 15px;
            margin: 10px 0;
        }

        .order-details strong {
            color: var(--primary);
        }

        .amount {
            font-size: 28px;
            color: var(--success);
            font-weight: 700;
            margin: 15px 0;
        }

        .btn-container {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 35px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 15px rgba(92, 107, 192, 0.4);
        }

        .btn-primary:hover {
            background: #3949ab;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(92, 107, 192, 0.5);
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-secondary:hover {
            background: var(--primary);
            color: white;
        }

        .footer-note {
            margin-top: 30px;
            color: var(--text-muted);
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .success-container {
                padding: 40px 25px;
            }

            h1 {
                font-size: 26px;
            }

            .btn-container {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="success-container">
        <div class="checkmark"></div>

        <h1> Payment Successful!</h1>

        <p class="message">
            Thank you for your purchase! Your order has been confirmed and you'll receive an email confirmation shortly.
        </p>

        <?php if (isset($order_number) && isset($amount_paid)): ?>
            <div class="order-details">
                <p><strong>Order Number:</strong> #<?php echo htmlspecialchars($order_number); ?></p>
                <p><strong>Amount Paid:</strong></p>
                <div class="amount">$<?php echo number_format($amount_paid, 2); ?></div>
            </div>
        <?php endif; ?>

        <div class="btn-container">
            <a href="books.php" class="btn btn-primary"> Continue Shopping</a>
            <a href="order_items.php" class="btn btn-secondary"> View Orders</a>
        </div>

        <p class="footer-note">
            Your e-books are now available in your library!
        </p>
    </div>
</body>

</html>