<?php
session_start();

// ⚠️ ADMIN ONLY ACCESS - Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['message'] = "Access Denied! Admin privileges required.";
    $_SESSION['message_type'] = "danger";
    header("Location: login.php");
    exit();
}

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

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $genre = trim($_POST['genre']);
    $stock = intval($_POST['stock']);

    // Handling file uploads
    $coverImage = $_FILES['cover_image']['name'];
    $fileUrl = $_FILES['file_url']['name'];

    $targetDir = "uploads/";

    // Create uploads directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $coverImagePath = $targetDir . basename($coverImage);
    $fileUrlPath = $targetDir . basename($fileUrl);

    // Validate file uploads
    $upload_success = true;

    if (
        move_uploaded_file($_FILES['cover_image']['tmp_name'], $coverImagePath) &&
        move_uploaded_file($_FILES['file_url']['tmp_name'], $fileUrlPath)
    ) {

        // Insert into database
        $sql = "INSERT INTO Books (title, author, description, price, genre, cover_image, file_url, stock) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdsssi", $title, $author, $description, $price, $genre, $coverImagePath, $fileUrlPath, $stock);

        if ($stmt->execute()) {
            $success_message = "Book added successfully!";
        } else {
            $error_message = "Database Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $error_message = "Error uploading files. Please check permissions for the uploads folder.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Book - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Poppins', sans-serif;
            padding: 20px;
        }

        .admin-navbar {
            background: linear-gradient(90deg, #667eea, #764ba2);
            padding: 15px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            border-radius: 10px;
        }

        .admin-navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-navbar a {
            color: white;
            text-decoration: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .admin-navbar a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
            font-weight: 700;
        }

        .form-container h2 i {
            color: #667eea;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type=file] {
            font-size: 100px;
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-input-label {
            display: block;
            padding: 12px;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input-label:hover {
            background: #e9ecef;
            border-color: #667eea;
        }

        .file-input-label i {
            color: #667eea;
            font-size: 1.5rem;
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <!-- Admin Navigation -->
    <div class="admin-navbar">
        <div class="container">
            <a href="admin_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <span style="color: white; font-weight: 600;">
                <i class="fas fa-user-shield"></i> Admin: <?php echo htmlspecialchars($_SESSION['user_name']); ?>
            </span>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="form-container">
        <h2><i class="fas fa-book-medical"></i> Add a New Book</h2>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label"><i class="fas fa-heading"></i> Book Title *</label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="Enter book title" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="author" class="form-label"><i class="fas fa-user-edit"></i> Author *</label>
                    <input type="text" id="author" name="author" class="form-control" placeholder="Enter author name" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label"><i class="fas fa-align-left"></i> Description</label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Enter book description"></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="price" class="form-label"><i class="fas fa-dollar-sign"></i> Price *</label>
                    <input type="number" id="price" name="price" step="0.01" class="form-control" placeholder="0.00" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="genre" class="form-label"><i class="fas fa-tag"></i> Genre</label>
                    <input type="text" id="genre" name="genre" class="form-control" placeholder="e.g., Fiction, Science">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="stock" class="form-label"><i class="fas fa-boxes"></i> Stock *</label>
                    <input type="number" id="stock" name="stock" class="form-control" placeholder="0" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="fas fa-image"></i> Cover Image *</label>
                    <div class="file-input-wrapper">
                        <label class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <div>Click to upload cover image</div>
                            <small class="text-muted">JPG, PNG (Max 5MB)</small>
                        </label>
                        <input type="file" id="cover_image" name="cover_image" accept="image/*" required>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><i class="fas fa-file-pdf"></i> Book File *</label>
                    <div class="file-input-wrapper">
                        <label class="file-input-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <div>Click to upload book file</div>
                            <small class="text-muted">PDF, EPUB (Max 50MB)</small>
                        </label>
                        <input type="file" id="file_url" name="file_url" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-plus-circle"></i> Add Book to Store
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update file input labels with selected filenames
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || 'No file chosen';
                const label = e.target.previousElementSibling;
                label.querySelector('div').textContent = fileName;
            });
        });
    </script>
</body>

</html>