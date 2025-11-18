<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Database connection
$conn = new mysqli("localhost", "root", "", "sundar_swadesh");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add book to cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    $book_id = $_POST['book_id'];
    $quantity = 1;
    $check_sql = "SELECT * FROM Cart WHERE user_id = ? AND book_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $book_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $update_sql = "UPDATE Cart SET quantity = quantity + 1 WHERE user_id = ? AND book_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $user_id, $book_id);
        $update_stmt->execute();
    } else {
        $insert_sql = "INSERT INTO Cart (user_id, book_id, quantity) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("iii", $user_id, $book_id, $quantity);
        $insert_stmt->execute();
    }
    header("Location: cart.php");
    exit();
}

// Update cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cart_id => $quantity) {
        if ($quantity < 1) continue;
        $update_quantity = "UPDATE Cart SET quantity = ? WHERE cart_id = ? AND user_id = ?";
        $stmt = $conn->prepare($update_quantity);
        $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
        $stmt->execute();
    }
    header("Location: cart.php");
    exit();
}

// Remove item
if (isset($_GET['remove_id'])) {
    $remove_id = $_GET['remove_id'];
    $delete_sql = "DELETE FROM Cart WHERE cart_id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("ii", $remove_id, $user_id);
    $stmt->execute();
    header("Location: cart.php");
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Shopping Cart | Sundar Swadesh</title>
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
            color: #fff !important;
            font-weight: 600;
        }

        .nav-link {
            color: #fff !important;
            font-weight: 500;
            margin-right: 15px;
        }

        .nav-link:hover {
            color: #d1e3ff !important;
        }

        .btn-outline-light {
            border-radius: 6px;
        }

        /* Main Card */
        .container {
            max-width: 950px;
        }

        .card {
            background: rgba(255, 255, 255, 0.93);
            border: none;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background: linear-gradient(90deg, #4b6cb7, #182848);
            color: white;
        }

        .table tbody tr:hover {
            background-color: #f9fbff;
        }

        .btn-primary {
            background-color: #4b6cb7;
            border: none;
        }

        .btn-primary:hover {
            background-color: #3a58a1;
        }

        .btn-danger {
            border: none;
            border-radius: 8px;
        }

        .btn-finalize {
            background-color: #00b894;
            border: none;
            border-radius: 8px;
        }

        .btn-finalize:hover {
            background-color: #019874;
        }

        h2 {
            text-align: center;
            font-weight: 600;
            color: #182848;
            margin-bottom: 30px;
        }

        footer {
            text-align: center;
            margin-top: 70px;
            color: #777;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .table {
                font-size: 0.9rem;
            }

            .navbar .nav-link {
                margin-right: 10px;
            }

            h2 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="books.php">Sundar Swadesh</a>
            <div class="d-flex align-items-center">
                <a href="orders.php" class="nav-link">Order History</a>
                <a href="books.php" class="nav-link">Books</a>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Cart Section -->
    <div class="container">
        <div class="card">
            <h2>Your Shopping Cart</h2>

            <!-- Add Book -->
            <form method="POST" class="mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="book_id" class="form-label fw-semibold">Add a Book</label>
                        <select name="book_id" id="book_id" class="form-select" required>
                            <?php
                            $books_sql = "SELECT book_id, title FROM Books";
                            $books_result = $conn->query($books_sql);
                            while ($book = $books_result->fetch_assoc()) {
                                echo "<option value='{$book['book_id']}'>{$book['title']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-grid">
                        <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                    </div>
                </div>
            </form>

            <!-- Cart Items -->
            <?php if ($result->num_rows > 0): ?>
                <form method="POST" action="cart.php">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead>
                                <tr>
                                    <th>Book Title</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()):
                                    $total += $row['total_price']; ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['title']); ?></td>
                                        <td>$ <?= number_format($row['price'], 2); ?></td>
                                        <td><input type="number" name="quantity[<?= $row['cart_id']; ?>]" value="<?= $row['quantity']; ?>" min="1" class="form-control text-center"></td>
                                        <td>$ <?= number_format($row['total_price'], 2); ?></td>
                                        <td><a href="cart.php?remove_id=<?= $row['cart_id']; ?>" class="btn btn-danger btn-sm">Remove</a></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap">
                        <h5 class="fw-bold mb-3 mb-md-0">Total Price: $ <?= number_format($total, 2); ?></h5>
                        <div>
                            <button type="submit" name="update_cart" class="btn btn-primary me-2">Update Cart</button>
                            <a href="order_items.php" class="btn btn-finalize">Check Order</a>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <p class="text-center text-muted mt-3">Your cart is currently empty. Start shopping now!</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        © <?= date('Y'); ?> Sundar Swadesh | All Rights Reserved
    </footer>

</body>

</html>

<?php $conn->close(); ?>