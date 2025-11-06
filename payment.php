<?php
session_start();
require 'vendor/autoload.php';
require 'config.php'; // this now provides $stripe_keys array

// Prevent browser from caching old POST data
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

// ✅ PHASE 1: Create Stripe session (only once)
if (!isset($_GET['session_id'])) {
    $sql = "SELECT SUM(Books.price * Cart.quantity) AS total_price
            FROM Cart
            JOIN Books ON Cart.book_id = Books.book_id
            WHERE Cart.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_price = $row['total_price'] ?? 0;

    if ($total_price <= 0) {
        die("Your cart is empty. Please add items before proceeding.");
    }

    // Use secret key from $stripe_keys array
    \Stripe\Stripe::setApiKey($stripe_keys['secret']);
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

    try {
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'E-Book Purchase'],
                    'unit_amount' => $total_price * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $base_url . '/payment_success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $base_url . '/cart.php',
        ]);

        header("Location: payment.php?session_id=" . urlencode($checkout_session->id) . "&amount=" . urlencode($total_price));
        exit();
    } catch (\Stripe\Exception\ApiErrorException $e) {
        die("Payment Error: " . $e->getMessage());
    }
}

// ✅ PHASE 2: Safe page reload
$session_id = $_GET['session_id'];
$total_price = $_GET['amount'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Sundar Swadesh</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        :root {
            --primary: #5c6bc0;
            --primary-dark: #3949ab;
            --bg-light: #f4f6f8;
            --text-dark: #333;
            --text-muted: #666;
            --card-bg: #fff;
            --shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            --radius: 12px;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            margin: 0;
            padding: 0;
        }

        /* Navbar */
        .navbar {
            background-color: var(--primary);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 14px 0;
            box-shadow: var(--shadow);
        }

        .navbar a {
            color: #fff;
            text-decoration: none;
            padding: 12px 20px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .navbar a:hover {
            background-color: var(--primary-dark);
            border-radius: var(--radius);
        }

        /* Main Container */
        .container {
            max-width: 480px;
            margin: 80px auto;
            padding: 40px;
            background: var(--card-bg);
            box-shadow: var(--shadow);
            border-radius: var(--radius);
            text-align: center;
        }

        h2 {
            color: var(--primary-dark);
            margin-bottom: 20px;
        }

        p {
            color: var(--text-muted);
            font-size: 18px;
            margin-bottom: 30px;
        }

        .amount {
            font-size: 22px;
            color: var(--text-dark);
            font-weight: 600;
        }

        /* Pay Now Button */
        button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 14px 28px;
            font-size: 18px;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: var(--shadow);
        }

        button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* Footer */
        footer {
            text-align: center;
            margin-top: 60px;
            color: var(--text-muted);
            font-size: 14px;
        }

        @media (max-width: 600px) {
            .container {
                margin: 60px 20px;
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="navbar">
        <a href="books.php">📚 Books</a>
        <a href="cart.php">🛒 Cart</a>
        <a href="order_items.php">📦 Orders</a>
        <a href="orders.php">📖 History</a>
    </div>

    <div class="container">
        <h2>Secure Checkout</h2>
        <p>Total Amount</p>
        <div class="amount">$<?php echo number_format($total_price, 2); ?></div>
        <br>
        <button id="checkout-button">Pay Now</button>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> Sundar Swadesh. All Rights Reserved.
    </footer>

    <script>
        // Use the publishable key from $stripe_keys array
        const stripe = Stripe("<?php echo $stripe_keys['publishable']; ?>");

        document.getElementById("checkout-button").addEventListener("click", () => {
            stripe.redirectToCheckout({
                sessionId: "<?php echo htmlspecialchars($session_id); ?>"
            });
        });
    </script>
</body>

</html>