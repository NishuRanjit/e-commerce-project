<?php
session_start();

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sundar_swadesh";

// Create connection
try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sundar Swodesh Prakasan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --dark: #2c3e50;
            --light: #f8f9fa;
            --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--dark);
        }

        /* Navbar */
        .navbar {
            background: rgba(44, 62, 80, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 1.4rem;
            color: white !important;
        }

        .navbar-brand img {
            height: 45px;
            border-radius: 8px;
        }

        .auth-buttons {
            display: flex;
            gap: 12px;
        }

        .btn-login,
        .btn-register {
            padding: 10px 28px;
            border-radius: 25px;
            font-weight: 500;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-login {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-login:hover {
            background: var(--gradient);
            color: white;
            border-color: transparent;
        }

        .btn-register {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
            color: white;
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero-section {
            min-height: 85vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gradient);
            padding: 80px 20px;
        }

        .hero-content {
            text-align: center;
            max-width: 800px;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 40px;
            font-weight: 300;
        }

        .hero-btn {
            background: white;
            color: var(--primary);
            padding: 16px 45px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .hero-btn:hover {
            color: white;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        /* Features Section */
        .features-section {
            padding: 80px 20px;
            background: white;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 60px;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
            margin-bottom: 30px;
            border: 1px solid #f0f0f0;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 12px;
        }

        .feature-desc {
            color: #666;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        /* Footer */
        footer {
            background: rgba(44, 62, 80, 0.95);
            color: white;
            padding: 50px 0 30px;
        }

        .footer-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: white;
        }

        .footer-text {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.7;
            font-size: 0.95rem;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: block;
            margin-bottom: 12px;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: #f093fb;
        }

        .footer-link i {
            width: 25px;
        }

        .footer-bottom {
            margin-top: 40px;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .hero-btn {
                padding: 14px 35px;
                font-size: 1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .auth-buttons {
                flex-direction: column;
                width: 100%;
            }

            .btn-login,
            .btn-register {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="uploads/Port logo (1).jpg" alt="Logo">
                Sundar Swodesh Prakasan
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <div class="auth-buttons">
                    <a href="login.php" class="btn-login">Login</a>
                    <a href="user_registration.php" class="btn-register">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Welcome to Sundar Swodesh Prakasan</h1>
            <p class="hero-subtitle">Your destination for quality books and endless knowledge</p>
            <a href="login.php" class="hero-btn">Browse Books</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Why Choose Us</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3 class="feature-title">Vast Collection</h3>
                        <p class="feature-desc">Discover thousands of books across various genres and categories</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h3 class="feature-title">Fast Delivery</h3>
                        <p class="feature-desc">Get your books delivered quickly and safely to your doorstep</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="feature-title">Quality Assured</h3>
                        <p class="feature-desc">We ensure the highest quality books at the best prices</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <h5 class="footer-title">About Us</h5>
                    <p class="footer-text">We are passionate about books and committed to bringing the best reading experience to our valued customers.</p>
                </div>
                <div class="col-md-6 mb-4">
                    <h5 class="footer-title">Contact</h5>
                    <a href="#" class="footer-link">
                        <i class="fas fa-map-marker-alt"></i> Dillibazar, Kathmandu
                    </a>
                    <a href="tel:+9779823338021" class="footer-link">
                        <i class="fas fa-phone"></i> +977 9823338021
                    </a>
                    <a href="mailto:info@sundarswodesh.com" class="footer-link">
                        <i class="fas fa-envelope"></i> info@sundarswodesh.com
                    </a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Sundar Swodesh Prakasan. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
$conn->close();
?>