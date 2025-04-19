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

// Fetch books from the database
$sql = "SELECT * FROM Books";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Store | Sundar Swodesh Prakasan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #e74c3c;
            --accent-color: #3498db;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .navbar {
            background-color: var(--primary-color);
        }
        
        .book-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 15px;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 40px;
            color: var(--primary-color);
            text-align: center;
            position: relative;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background-color: var(--secondary-color);
        }
        
        .book-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            height: 100%;
        }
        
        .book-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
        }
        
        .book-cover {
            height: 300px;
            object-fit: cover;
            width: 100%;
        }
        
        .card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            height: calc(100% - 300px);
        }
        
        .book-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        
        .book-author {
            color: #6c757d;
            margin-bottom: 10px;
            font-style: italic;
        }
        
        .book-description {
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 15px;
            flex-grow: 1;
        }
        
        .book-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        
        .book-genre {
            display: inline-block;
            background-color: #f1f1f1;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #555;
            margin-bottom: 15px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: auto;
        }
        
        .btn-cart {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            padding: 8px 20px;
        }
        
        .btn-review {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 8px 20px;
        }
        
        .btn-cart:hover {
            background-color: #c0392b;
            border-color: #c0392b;
        }
        
        .btn-review:hover {
            background-color: #1a252f;
            border-color: #1a252f;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .book-cover {
                height: 250px;
            }
            
            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="img/logo.png" alt="Sundar Swodesh Prakasan" height="40">
            </a>
            <div class="d-flex align-items-center">
                <a href="logout.php" class="btn btn-outline-light">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="book-container">
        <h1 class="page-title">Our Book Collection</h1>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="col-lg-4 col-md-6">
                <div class="card book-card">
                    <img src="<?php echo htmlspecialchars($row['cover_image']); ?>" class="book-cover" alt="Book Cover">
                    <div class="card-body">
                        <h5 class="book-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                        <p class="book-author">By <?php echo htmlspecialchars($row['author']); ?></p>
                        <p class="book-description"><?php echo htmlspecialchars($row['description']); ?></p>
                        <span class="book-genre"><?php echo htmlspecialchars($row['genre']); ?></span>
                        <p class="book-price">$<?php echo htmlspecialchars($row['price']); ?></p>
                        <div class="action-buttons">
                            <a href="cart.php?book_id=<?php echo $row['book_id']; ?>" class="btn btn-cart">
                                <i class="fas fa-shopping-cart me-1"></i> Add to Cart
                            </a>
                            <a href="reviews.php?book_id=<?php echo $row['book_id']; ?>" class="btn btn-review">
                                <i class="fas fa-pen me-1"></i> Review
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> Sundar Swodesh Prakasan. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>