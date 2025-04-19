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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $genre = $_POST['genre'];
    $stock = $_POST['stock'];
    
    // Handling file uploads
    $coverImage = $_FILES['cover_image']['name'];
    $fileUrl = $_FILES['file_url']['name'];
    
    $targetDir = "uploads/";
    $coverImagePath = $targetDir . basename($coverImage);
    $fileUrlPath = $targetDir . basename($fileUrl);
    
    move_uploaded_file($_FILES['cover_image']['tmp_name'], $coverImagePath);
    move_uploaded_file($_FILES['file_url']['tmp_name'], $fileUrlPath);
    
    // Insert into database
    $sql = "INSERT INTO Books (title, author, description, price, genre, cover_image, file_url, stock) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $title, $author, $description, $price, $genre, $coverImagePath, $fileUrlPath, $stock);
    
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Book added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
    
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Book</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container label {
            font-weight: bold;
        }
        .form-container input[type="text"],
        .form-container input[type="number"],
        .form-container textarea,
        .form-container input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-container input[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add a New Book</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Title:</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="author" class="form-label">Author:</label>
                <input type="text" id="author" name="author" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description:</label>
                <textarea id="description" name="description" class="form-control" rows="4"></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price:</label>
                <input type="number" id="price" name="price" step="0.01" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="genre" class="form-label">Genre:</label>
                <input type="text" id="genre" name="genre" class="form-control">
            </div>
            <div class="mb-3">
                <label for="stock" class="form-label">Stock:</label>
                <input type="number" id="stock" name="stock" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="cover_image" class="form-label">Cover Image:</label>
                <input type="file" id="cover_image" name="cover_image" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="file_url" class="form-label">Book File:</label>
                <input type="file" id="file_url" name="file_url" class="form-control" required>
            </div>
            <div class="mb-3">
                <input type="submit" value="Add Book" class="btn btn-success">
            </div>
        </form>
    </div>
</body>
</html>