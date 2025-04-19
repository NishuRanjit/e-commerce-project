<?php
// Enable error reporting (optional, for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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

// Add book to cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    $book_id = $_POST['book_id'];
    $quantity = 1; // Default quantity

    // Check if the book is already in the cart
    $check_sql = "SELECT * FROM Cart WHERE user_id = ? AND book_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $book_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Update quantity if book is already in the cart
        $update_sql = "UPDATE Cart SET quantity = quantity + 1 WHERE user_id = ? AND book_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $user_id, $book_id);
        $update_stmt->execute();
    } else {
        // Insert new book into the cart
        $insert_sql = "INSERT INTO Cart (user_id, book_id, quantity) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iii", $user_id, $book_id, $quantity);
        $insert_stmt->execute();
    }

    header("Location: cart.php"); // Redirect back to cart page
    exit();
}

// Update cart quantity
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cart_id => $quantity) {
        // Validate quantity
        if ($quantity < 1) {
            continue;
        }

        // Update quantity in the database
        $update_quantity = "UPDATE Cart SET quantity = ? WHERE cart_id = ? AND user_id = ?";
        $stmt = $conn->prepare($update_quantity);
        $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
        $stmt->execute();
    }
    header("Location: cart.php"); // Redirect back to cart page
    exit();
}

// Remove item from cart
if (isset($_GET['remove_id'])) {
    $remove_id = $_GET['remove_id'];
    $delete_sql = "DELETE FROM Cart WHERE cart_id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $remove_id, $user_id);
    $stmt->execute();
    header("Location: cart.php"); // Redirect back to cart page
    exit();
}

// Fetch cart items
$sql = "SELECT Cart.cart_id, Books.title, Books.price, Cart.quantity, (Books.price * Cart.quantity) AS total_price
        FROM Cart
        JOIN Books ON Cart.book_id = Books.book_id
        WHERE Cart.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Your Cart</h2>

        <!-- Add Book to Cart Form -->
        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label for="book_id" class="form-label">Add Book to Cart</label>
                <select name="book_id" id="book_id" class="form-control" required>
                    <?php
                    // Fetch all books from the database
                    $books_sql = "SELECT book_id, title FROM Books";
                    $books_result = $conn->query($books_sql);
                    while ($book = $books_result->fetch_assoc()) {
                        echo "<option value='{$book['book_id']}'>{$book['title']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
        </form>

        <!-- Display Cart Items -->
        <?php if ($result->num_rows > 0) { ?>
            <form method="POST" action="order_items.php">
                <table class="table table-bordered">
                    <tr>
                        <th>Book Title</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()) { 
                        $total += $row['total_price'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td>$<?php echo number_format($row['price'], 2); ?></td>
                        <td>
                            <input type="number" name="quantity[<?php echo $row['cart_id']; ?>]" value="<?php echo $row['quantity']; ?>" min="1" class="form-control">
                        </td>
                        <td>$<?php echo number_format($row['total_price'], 2); ?></td>
                        <td>
                            <a href="cart.php?remove_id=<?php echo $row['cart_id']; ?>" class="btn btn-danger btn-sm">Remove</a>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total Price:</strong></td>
                        <td colspan="2"><strong>$<?php echo number_format($total, 2); ?></strong></td>
                    </tr>
                </table>
                <button type="submit" name="update_cart" class="btn btn-primary">Update Cart</button>
                <button type="submit" name="proceed_to_checkout" class="btn btn-success">Proceed to Checkout</button>
            </form>
        <?php } else { ?>
            <p>Your cart is empty.</p>
        <?php } ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>