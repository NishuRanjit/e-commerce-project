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



// Handle Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format!";
        $_SESSION['message_type'] = "danger";
    } else {
        $sql = "SELECT user_id, name, email, password, role FROM Users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($user_id, $name, $email, $hashed_password, $role);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = $role;

                    $_SESSION['message'] = "Login successful!";
                    $_SESSION['message_type'] = "success";
                    header("Location: books.php");
                    exit();
                } else {
                    $_SESSION['message'] = "Incorrect password!";
                    $_SESSION['message_type'] = "danger";
                }
            } else {
                $_SESSION['message'] = "User not found!";
                $_SESSION['message_type'] = "danger";
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = "Error preparing statement: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sundar Swodesh Prakasan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('img/login-bg.jpg');
            background-size: cover;
            background-position: center;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo img {
            height: 60px;
        }
        .form-control {
            height: 50px;
            border-radius: 5px;
            padding-left: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            border-color: #2c3e50;
            box-shadow: none;
        }
        .btn-login {
            background-color: #e74c3c;
            border: none;
            height: 50px;
            font-weight: 600;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background-color: #c0392b;
            transform: translateY(-3px);
        }
        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
        }
        .login-footer a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 500;
        }
        .divider {
            position: relative;
            margin: 30px 0;
            text-align: center;
            color: #999;
        }
        .divider:before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #ddd;
            z-index: -1;
        }
        .divider span {
            background-color: #fff;
            padding: 0 15px;
            position: relative;
        }
        .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        .social-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            transition: all 0.3s;
        }
        .social-btn:hover {
            transform: translateY(-3px);
        }
        .facebook {
            background-color: #3b5998;
        }
        .google {
            background-color: #db4437;
        }
        .twitter {
            background-color: #1da1f2;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <img src="img/logo.png" alt="Sundar Swodesh Prakasan">
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email Address" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <a href="forgot_password.php" class="text-decoration-none">Forgot password?</a>
            </div>
            <button type="submit" name="login" class="btn btn-primary btn-login w-100">Log In</button>
        </form>

        <div class="divider">
            <span>OR</span>
        </div>

        <div class="social-login">
            <a href="#" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-btn google"><i class="fab fa-google"></i></a>
            <a href="#" class="social-btn twitter"><i class="fab fa-twitter"></i></a>
        </div>

        <div class="login-footer">
            Don't have an account? <a href="user_registration.php">Create one</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>