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
$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;

// Fetch book details
$book_sql = "SELECT * FROM Books WHERE book_id = ?";
$book_stmt = $conn->prepare($book_sql);
$book_stmt->bind_param("i", $book_id);
$book_stmt->execute();
$book_result = $book_stmt->get_result();
$book = $book_result->fetch_assoc();
$book_stmt->close();

if (!$book) {
    $_SESSION['message'] = "Book not found!";
    $_SESSION['message_type'] = "danger";
    header("Location: books.php");
    exit();
}

// Handle Review Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    $rating = intval($_POST['rating']);
    $review_text = htmlspecialchars(trim($_POST['review_text']));

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        $_SESSION['message'] = "Rating must be between 1 and 5!";
        $_SESSION['message_type'] = "danger";
    } else {
        // Insert review into the database
        $insert_sql = "INSERT INTO Reviews (user_id, book_id, rating, review_text) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        if ($insert_stmt) {
            $insert_stmt->bind_param("iiis", $user_id, $book_id, $rating, $review_text);

            if ($insert_stmt->execute()) {
                $_SESSION['message'] = "Review submitted successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: books.php");
                exit();
            } else {
                $_SESSION['message'] = "Error submitting review: " . $insert_stmt->error;
                $_SESSION['message_type'] = "danger";
            }
            $insert_stmt->close();
        } else {
            $_SESSION['message'] = "Error preparing statement: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
    }
}

// Fetch existing reviews for the book
$reviews_sql = "SELECT Reviews.*, Users.name FROM Reviews JOIN Users ON Reviews.user_id = Users.user_id WHERE book_id = ? ORDER BY review_date DESC";
$reviews_stmt = $conn->prepare($reviews_sql);
$reviews_stmt->bind_param("i", $book_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
$reviews_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write a Review</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 0;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Navigation Bar */
        .navbar {
            background: rgba(44, 62, 80, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
            text-decoration: none;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .container {
            max-width: 900px;
            margin-top: 40px;
            padding-bottom: 40px;
        }

        .card {
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            font-size: 1.3rem;
        }

        .card-body {
            padding: 25px;
            background: white;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .review-card {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            background: white;
            transition: all 0.3s ease;
        }

        .review-card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .review-author {
            font-weight: bold;
            color: #667eea;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        .review-rating {
            color: #ffc107;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .review-date {
            font-size: 0.9rem;
            color: #666;
            margin-top: 10px;
        }

        .review-text {
            color: #555;
            line-height: 1.6;
            margin: 10px 0;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
        }

        h4 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 20px;
            padding-left: 15px;
            border-left: 4px solid #667eea;
        }

        .star {
            color: #ffc107;
        }

        .star-empty {
            color: #ddd;
        }

        .no-reviews {
            text-align: center;
            color: #999;
            padding: 40px 0;
            font-size: 1.1rem;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container-fluid">
            <a href="books.php" class="navbar-brand">
                Sundar Swodesh Prakasan
            </a>
            <a href="books.php" class="btn-back">
                ← Back to Books
            </a>
        </div>
    </nav>

    <div class="container">
        <!-- Display Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']);
            unset($_SESSION['message_type']); ?>
        <?php endif; ?>

        <!-- Book Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h4 style="margin: 0; border: none; padding: 0; color: white;">
                    Write a Review for: <?php echo htmlspecialchars($book['title']); ?>
                </h4>
            </div>
            <div class="card-body">
                <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($book['description']); ?></p>
            </div>
        </div>

        <!-- Review Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="rating" class="form-label">
                            Rating (1-5):
                        </label>
                        <input type="number" name="rating" class="form-control" min="1" max="5" required>
                    </div>
                    <div class="form-group">
                        <label for="review_text" class="form-label">
                            Your Review:
                        </label>
                        <textarea name="review_text" class="form-control" rows="5" placeholder="Share your thoughts about this book..." required></textarea>
                    </div>
                    <button type="submit" name="submit_review" class="btn btn-primary">
                        Submit Review
                    </button>
                </form>
            </div>
        </div>

        <!-- Existing Reviews -->
        <h4>Existing Reviews</h4>
        <?php if ($reviews_result->num_rows > 0): ?>
            <?php while ($review = $reviews_result->fetch_assoc()): ?>
                <div class="review-card">
                    <div class="review-author">
                        <?php echo htmlspecialchars($review['name']); ?>
                    </div>
                    <div class="review-rating">
                        <?php
                        $rating = intval($review['rating']);
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $rating) {
                                echo '<span class="star">★</span>';
                            } else {
                                echo '<span class="star-empty">★</span>';
                            }
                        }
                        echo " ({$rating}/5)";
                        ?>
                    </div>
                    <div class="review-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></div>
                    <div class="review-date">
                        <?php echo date('M d, Y', strtotime($review['review_date'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-reviews">
                No reviews yet. Be the first to write one!
            </p>
        <?php endif; ?>
    </div>
</body>

</html>

<?php
$conn->close();
?>