<?php
session_start();

// Database connection details
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "sundar_swadesh";


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = 'customer';

    // Validate inputs
    $errors = [];

    if (empty($name)) {
        $errors[] = "Name is required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Password validation
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }
    if (!preg_match('/[\W_]/', $password)) {
        $errors[] = "Password must contain at least one special character.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Check if email exists
        $check_sql = "SELECT email FROM Users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $_SESSION['message'] = "Email already exists!";
            $_SESSION['message_type'] = "danger";
        } else {
            // Store plain password (NO hashing)
            $insert_sql = "INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssss", $name, $email, $password, $role);

            if ($insert_stmt->execute()) {
                $_SESSION['message'] = "Registration successful! Please log in.";
                $_SESSION['message_type'] = "success";
                header("Location: login.php");
                exit();
            } else {
                $_SESSION['message'] = "Error: " . $insert_stmt->error;
                $_SESSION['message_type'] = "danger";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Sundar Swodesh Prakasan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('img/register-bg.jpg');
            background-size: cover;
            background-position: center;
        }

        .register-container {
            max-width: 500px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .register-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-logo img {
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

        .btn-register {
            background-color: #e74c3c;
            border: none;
            height: 50px;
            font-weight: 600;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .btn-register:hover {
            background-color: #c0392b;
            transform: translateY(-3px);
        }

        .register-footer {
            text-align: center;
            margin-top: 20px;
            color: #777;
        }

        .register-footer a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 500;
        }

        .password-strength {
            height: 5px;
            background-color: #eee;
            margin-bottom: 20px;
            border-radius: 5px;
            overflow: hidden;
        }

        .strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s;
        }

        .password-requirements {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 20px;
        }

        .password-requirements li.valid {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register-logo">
            <img src="uploads\Port logo (1).jpg" alt="Sundar Swodesh Prakasan">
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['message']);
            unset($_SESSION['message_type']); ?>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <input type="text" name="name" class="form-control" placeholder="Full Name" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email Address" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                <div class="password-strength">
                    <div class="strength-meter" id="strength-meter"></div>
                </div>
                <div class="password-requirements">
                    <p>Password must contain:</p>
                    <ul>
                        <li id="length">At least 8 characters</li>
                        <li id="uppercase">One uppercase letter</li>
                        <li id="lowercase">One lowercase letter</li>
                        <li id="number">One number</li>
                        <li id="special">One special character</li>
                    </ul>
                </div>
            </div>
            <div class="mb-3">
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label" for="terms">I agree to the
                    <a href="terms.php">Terms of Service</a> and
                    <a href="privacy.php">Privacy Policy</a>
                </label>
            </div>
            <button type="submit" name="register" class="btn btn-primary btn-register w-100">Register</button>
        </form>

        <div class="register-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById("password");
        const meter = document.getElementById("strength-meter");
        const checks = {
            length: document.getElementById("length"),
            uppercase: document.getElementById("uppercase"),
            lowercase: document.getElementById("lowercase"),
            number: document.getElementById("number"),
            special: document.getElementById("special"),
        };

        passwordInput.addEventListener("input", () => {
            const val = passwordInput.value;
            let strength = 0;
            const conditions = {
                length: val.length >= 8,
                uppercase: /[A-Z]/.test(val),
                lowercase: /[a-z]/.test(val),
                number: /[0-9]/.test(val),
                special: /[\W_]/.test(val),
            };
            for (const key in conditions) {
                if (conditions[key]) {
                    checks[key].classList.add("valid");
                    strength++;
                } else {
                    checks[key].classList.remove("valid");
                }
            }
            const percent = (strength / 5) * 100;
            meter.style.width = percent + "%";
            meter.style.backgroundColor = percent < 40 ? "red" : percent < 80 ? "orange" : "green";
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>