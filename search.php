<?php
// search.php - Complete Search Functionality
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "sundar_swadesh");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$books = [];
$message = '';

if (!empty($search_query)) {
    // Log search activity
    $log_stmt = $conn->prepare("INSERT INTO user_activity (user_id, activity_type, search_query) VALUES (?, 'search', ?)");
    if ($log_stmt) {
        $log_stmt->bind_param("is", $user_id, $search_query);
        $log_stmt->execute();
        $log_stmt->close();
    }

    // Search for books (title, author, genre, description)
    $search_term = "%{$search_query}%";
    $sql = "SELECT * FROM books 
            WHERE title LIKE ? 
               OR author LIKE ? 
               OR genre LIKE ? 
               OR description LIKE ?
            ORDER BY 
                CASE 
                    WHEN title LIKE ? THEN 1
                    WHEN author LIKE ? THEN 2
                    WHEN genre LIKE ? THEN 3
                    ELSE 4
                END,
                title ASC
            LIMIT 50";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssssss", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
            $message = "Found " . count($books) . " result(s) for '" . htmlspecialchars($search_query) . "'";
        } else {
            $message = "No results found for '" . htmlspecialchars($search_query) . "'";
        }
        $stmt->close();
    }
} else {
    $message = "Please enter a search query";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | Sundar Swadesh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f4f9ff, #eefaf1);
            font-family: 'Poppins', sans-serif;
            color: #2c3e50;
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(90deg, #182848, #4b6cb7);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .navbar-brand {
            color: #fff !important;
            font-weight: 600;
            font-size: 1.3rem;
        }

        .nav-link {
            color: #fff !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: #ffd700 !important;
            transform: translateY(-2px);
        }

        /* Search Bar */
        .search-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .search-box {
            display: flex;
            background: white;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .search-box input {
            flex: 1;
            border: none;
            padding: 15px 25px;
            font-size: 16px;
            outline: none;
        }

        .search-box button {
            background: #4b6cb7;
            border: none;
            padding: 0 30px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-box button:hover {
            background: #3a58a1;
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            margin-top: 30px;
            padding: 20px;
        }

        .search-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .search-message {
            font-size: 1.2rem;
            color: #333;
            margin-top: 20px;
        }

        /* Book Cards */
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }

        .book-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            cursor: pointer;
        }

        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .book-cover {
            width: 100%;
            height: 300px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .book-body {
            padding: 20px;
        }

        .book-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .book-author {
            color: #7f8c8d;
            font-size: 0.95rem;
            margin-bottom: 8px;
            font-style: italic;
        }

        .book-genre {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-bottom: 12px;
        }

        .book-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 15px;
        }

        .btn-cart {
            background: #4b6cb7;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-cart:hover {
            background: #3a58a1;
            color: white;
            transform: scale(1.05);
        }

        .highlight {
            background-color: yellow;
            font-weight: bold;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
        }

        .no-results i {
            font-size: 80px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .no-results h3 {
            color: #666;
            margin-bottom: 15px;
        }

        .no-results p {
            color: #999;
        }

        footer {
            text-align: center;
            margin-top: 60px;
            padding: 20px;
            color: #777;
        }

        @media (max-width: 768px) {
            .books-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .search-box input {
                padding: 12px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="books.php">
                <i class="fas fa-book-open"></i> Sundar Swadesh
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="books.php"><i class="fas fa-book"></i> Books</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php"><i class="fas fa-box"></i> Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <!-- Search Header -->
        <div class="search-header">
            <h2 class="text-center mb-4"><i class="fas fa-search"></i> Search Books</h2>

            <div class="search-container">
                <form method="GET" action="search.php">
                    <div class="search-box">
                        <input
                            type="text"
                            name="q"
                            placeholder="Search by title, author, or genre..."
                            value="<?php echo htmlspecialchars($search_query); ?>"
                            autofocus
                            required>
                        <button type="submit">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>

            <?php if (!empty($message)): ?>
                <p class="search-message text-center">
                    <i class="fas fa-info-circle"></i> <?php echo $message; ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Search Results -->
        <?php if (!empty($books)): ?>
            <div class="books-grid">
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <img
                            src="<?php echo htmlspecialchars($book['cover_image'] ?: 'uploads/placeholder.jpg'); ?>"
                            class="book-cover"
                            alt="<?php echo htmlspecialchars($book['title']); ?>"
                            onerror="this.src='uploads/placeholder.jpg'">
                        <div class="book-body">
                            <h5 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                            <p class="book-author">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($book['author']); ?>
                            </p>
                            <?php if (!empty($book['genre'])): ?>
                                <span class="book-genre">
                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($book['genre']); ?>
                                </span>
                            <?php endif; ?>
                            <div class="book-price">
                                $<?php echo number_format($book['price'], 2); ?>
                            </div>
                            <a href="cart.php?book_id=<?php echo $book['book_id']; ?>" class="btn-cart">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (!empty($search_query)): ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>No Books Found</h3>
                <p>Try searching with different keywords</p>
                <a href="books.php" class="btn btn-primary mt-3">
                    <i class="fas fa-arrow-left"></i> Browse All Books
                </a>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> Sundar Swadesh. All Rights Reserved.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php $conn->close(); ?>