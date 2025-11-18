<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sundar_swadesh";

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

// Handle Add to Cart from URL (when clicking Add button)
if (isset($_GET['add_to_cart'])) {
    $book_id = $_GET['add_to_cart'];

    // Check if book already in cart
    $check_sql = "SELECT cart_id, quantity FROM Cart WHERE user_id = ? AND book_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $book_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Update quantity
        $update_sql = "UPDATE Cart SET quantity = quantity + 1 WHERE user_id = ? AND book_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $user_id, $book_id);
        $update_stmt->execute();
        $_SESSION['cart_message'] = "Book quantity updated in cart!";
    } else {
        // Insert new item
        $insert_sql = "INSERT INTO Cart (user_id, book_id, quantity) VALUES (?, ?, 1)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $user_id, $book_id);
        $insert_stmt->execute();
        $_SESSION['cart_message'] = "Book added to cart successfully!";
    }

    header("Location: books.php?added=1");
    exit();
}

// Get cart count for badge
$cart_count_sql = "SELECT SUM(quantity) as total FROM Cart WHERE user_id = ?";
$cart_stmt = $conn->prepare($cart_count_sql);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_count_result = $cart_stmt->get_result();
$cart_count = $cart_count_result->fetch_assoc()['total'] ?? 0;

// Fetch all books
$books = [];
$sql = "SELECT * FROM books ORDER BY book_id DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Books | Sundar Swadesh Prakasan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #5568d3;
            --secondary: #764ba2;
            --accent: #f093fb;
            --danger: #ff6b6b;
            --success: #51cf66;
            --dark: #2c3e50;
            --light: #f8f9fa;
            --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 15px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.15);
            --shadow-xl: 0 15px 50px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Poppins', sans-serif;
            color: var(--dark);
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        /* Navbar */
        .navbar {
            background: rgba(44, 62, 80, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.2s ease;
        }

        .navbar-brand i {
            font-size: 1.8rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .navbar-brand:hover {
            transform: scale(1.02);
        }

        .nav-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-nav {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
            position: relative;
        }

        .btn-nav:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transform: translateY(-1px);
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--gradient-2);
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        /* Success Message */
        .success-message {
            position: fixed;
            top: 80px;
            right: 20px;
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            border-left: 4px solid var(--success);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .success-message i {
            color: var(--success);
            font-size: 1.5rem;
        }

        /* Search Section */
        .search-section {
            background: var(--gradient-1);
            padding: 50px 20px;
            position: relative;
            overflow: hidden;
        }

        .search-title {
            text-align: center;
            color: white;
            margin-bottom: 25px;
            font-weight: 700;
            font-size: 2rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .search-box-main {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .search-box-main form {
            display: flex;
            background: white;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            transition: all 0.2s ease;
        }

        .search-box-main form:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .search-box-main input {
            flex: 1;
            border: none;
            padding: 15px 25px;
            font-size: 15px;
            outline: none;
        }

        .search-box-main button {
            background: var(--gradient-2);
            border: none;
            padding: 0 35px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s ease;
            font-size: 15px;
        }

        .search-box-main button:hover {
            opacity: 0.9;
        }

        /* Book Container */
        .book-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 20px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .section-header h2 {
            font-weight: 700;
            font-size: 2rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .section-header p {
            color: #666;
            font-size: 0.95rem;
        }

        /* Row spacing */
        .row {
            row-gap: 10px;
        }

        /* Book Cards */
        .book-card {
            background: white;
            border: none;
            border-radius: 15px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
            height: 100%;
            margin-bottom: 35px;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .book-cover-wrapper {
            position: relative;
            overflow: hidden;
            height: 220px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .book-cover {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.2s ease;
        }

        .book-card:hover .book-cover {
            transform: scale(1.03);
        }

        .book-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--gradient-2);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
            box-shadow: var(--shadow-md);
            z-index: 1;
        }

        .card-body {
            padding: 18px;
        }

        .book-title {
            font-weight: 600;
            font-size: 1rem;
            color: var(--dark);
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 48px;
            line-height: 1.4;
        }

        .book-author {
            color: #7f8c8d;
            font-size: 0.85rem;
            margin-bottom: 10px;
            font-style: italic;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .book-description {
            font-size: 0.8rem;
            color: #666;
            line-height: 1.5;
            margin: 10px 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 48px;
        }

        .book-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #eee;
            flex-wrap: wrap;
            gap: 8px;
        }

        .book-price {
            font-size: 1.3rem;
            font-weight: 700;
            background: var(--gradient-2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .book-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-cart,
        .btn-review {
            background: var(--gradient-1);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
        }

        .btn-cart:hover,
        .btn-review:hover {
            color: white;
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-review {
            background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
            padding: 8px 14px;
        }

        .btn-review:hover {
            box-shadow: 0 4px 12px rgba(255, 216, 155, 0.4);
        }

        /* Recommendations */
        .recommend-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-top: 50px;
            box-shadow: var(--shadow-lg);
            display: none;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
        }

        .recommend-section.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .recommend-section h3 {
            font-weight: 700;
            font-size: 1.7rem;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            padding-left: 15px;
            margin-bottom: 25px;
            position: relative;
        }

        .recommend-section h3::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: var(--gradient-1);
            border-radius: 5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .search-title {
                font-size: 1.5rem;
            }

            .section-header h2 {
                font-size: 1.5rem;
            }

            .book-card {
                margin-bottom: 25px;
            }

            .btn-cart,
            .btn-review {
                padding: 7px 12px;
                font-size: 0.75rem;
            }

            .success-message {
                right: 10px;
                left: 10px;
            }
        }
    </style>
</head>

<body>

    <?php if (isset($_GET['added']) && isset($_SESSION['cart_message'])): ?>
        <div class="success-message" id="successMessage">
            <i class="fas fa-check-circle"></i>
            <div><?= $_SESSION['cart_message'] ?></div>
        </div>
        <?php unset($_SESSION['cart_message']); ?>
    <?php endif; ?>

    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a href="books.php" class="navbar-brand">
                <i class="fas fa-book-open"></i>
                Sundar Swodesh Prakasan
            </a>
            <div class="nav-buttons">
                <a href="cart.php" class="btn-nav">
                    Cart
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
                <a href="orders.php" class="btn-nav">
                    Orders
                </a>
                <a href="logout.php" class="btn-nav">
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Search Section -->
    <div class="search-section">
        <h2 class="search-title">Find Your Next Great Read</h2>
        <div class="search-box-main">
            <form method="GET" action="search.php">
                <input type="text" name="q" placeholder="Search by title, author, or genre..." required>
                <button type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="book-container">
        <div class="section-header">
            <h2>Explore Our Collection</h2>
            <p>Discover amazing books curated just for you</p>
        </div>

        <div class="row">
            <?php if (empty($books)): ?>
                <div class="col-12 text-center">
                    <p style="color: #999; font-size: 1.2rem; padding: 60px 0;">No books available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($books as $book): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="book-card" onclick="loadRecommendations(<?= $book['book_id'] ?>)">
                            <div class="book-cover-wrapper">
                                <img src="<?= htmlspecialchars($book['cover_image'] ?? 'uploads/placeholder.jpg') ?>"
                                    class="book-cover"
                                    alt="<?= htmlspecialchars($book['title']) ?>">
                                <span class="book-badge">NEW</span>
                            </div>
                            <div class="card-body">
                                <h5 class="book-title"><?= htmlspecialchars($book['title']) ?></h5>
                                <p class="book-author">
                                    <i class="fas fa-user-circle"></i>
                                    <?= htmlspecialchars($book['author']) ?>
                                </p>
                                <p class="book-description"><?= htmlspecialchars($book['description'] ?? 'No description available') ?></p>
                                <div class="book-footer">
                                    <div class="book-price">$<?= number_format($book['price'], 2) ?></div>
                                    <div class="book-actions">
                                        <a href="reviews.php?book_id=<?= $book['book_id'] ?>"
                                            class="btn-review"
                                            onclick="event.stopPropagation()"
                                            title="Write a Review">
                                            Review
                                        </a>
                                        <a href="books.php?add_to_cart=<?= $book['book_id'] ?>"
                                            class="btn-cart"
                                            onclick="event.stopPropagation()">
                                            Add to Cart
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="recommendationArea" class="recommend-section">
            <h3>Recommended For You</h3>
            <div id="recommendationContent" class="row"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Auto-hide success message
        <?php if (isset($_GET['added'])): ?>
            setTimeout(function() {
                $('#successMessage').fadeOut(300);
            }, 3000);
        <?php endif; ?>

        function loadRecommendations(bookId) {
            const content = $('#recommendationContent');
            content.html('<div class="text-center"><p>Loading recommendations...</p></div>');

            $.ajax({
                url: 'fetch_recommendations.php',
                type: 'GET',
                data: {
                    book_id: bookId
                },
                success: function(data) {
                    $('#recommendationArea').addClass('show');
                    content.html(data);
                    $('html, body').animate({
                        scrollTop: $('#recommendationArea').offset().top - 100
                    }, 800);
                },
                error: function() {
                    content.html('<p class="text-danger text-center">Failed to load recommendations.</p>');
                }
            });
        }
    </script>
</body>

</html>
<?php $conn->close(); ?>