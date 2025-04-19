<?php
session_start();
require 'vendor/autoload.php'; // Load Stripe SDK
require 'config.php'; // Stripe API keys

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

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch total price from cart
$sql = "SELECT SUM(Books.price * Cart.quantity) AS total_price
        FROM Cart
        JOIN Books ON Cart.book_id = Books.book_id
        WHERE Cart.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_price = $row['total_price'];

if ($total_price <= 0) {
    die("Your cart is empty. Please add items before proceeding.");
}

// Stripe session creation
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Construct base URL dynamically
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'E-Book Purchase',
                ],
                'unit_amount' => $total_price * 100, // Convert to cents
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => $base_url . '/payment_success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $base_url . '/cart.php',
    ]);

    // Redirect to Stripe Checkout
    header("Location: " . $checkout_session->url);
    exit();
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Handle error
    die("Error: " . $e->getMessage());
}
?>