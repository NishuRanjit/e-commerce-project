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
        body { background-color: #f8f9fa; padding: 20px; }
        .container { max-width: 800px; }
        .card { border: none; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .card-header { background-color: #007bff; color: white; }
        .card-body { padding: 20px; }
        .form-group { margin-bottom: 15px; }
        .btn { margin-top: 10px; }
        .review-card { margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .review-author { font-weight: bold; color: #007bff; }
        .review-rating { color: #ffc107; font-size: 1.1rem; }
        .review-date { font-size: 0.9rem; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Display Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        <?php endif; ?>

        <!-- Book Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Write a Review for: <?php echo htmlspecialchars($book['title']); ?></h4>
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
                        <label for="rating">Rating (1-5):</label>
                        <input type="number" name="rating" class="form-control" min="1" max="5" required>
                    </div>
                    <div class="form-group">
                        <label for="review_text">Review:</label>
                        <textarea name="review_text" class="form-control" rows="5" required></textarea>
                    </div>
                    <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                </form>
            </div>
        </div>

        <!-- Existing Reviews -->
        <h4>Existing Reviews</h4>
        <?php if ($reviews_result->num_rows > 0): ?>
            <?php while ($review = $reviews_result->fetch_assoc()): ?>
                <div class="review-card">
                    <div class="review-author"><?php echo htmlspecialchars($review['name']); ?></div>
                    <div class="review-rating">Rating: <?php echo htmlspecialchars($review['rating']); ?></div>
                    <div class="review-text"><?php echo htmlspecialchars($review['review_text']); ?></div>
                    <div class="review-date"><?php echo htmlspecialchars($review['review_date']); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No reviews yet. Be the first to write one!</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>