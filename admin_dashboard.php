<?php
session_start();

// ⚠️ ADMIN ONLY ACCESS - Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['message'] = "Access Denied! Admin privileges required.";
    $_SESSION['message_type'] = "danger";
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sundar_swadesh";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch statistics
$total_books = $conn->query("SELECT COUNT(*) as count FROM Books")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM Users WHERE role='customer'")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM Orders")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_price) as revenue FROM Orders WHERE payment_status='Paid'")->fetch_assoc()['revenue'] ?? 0;

// Fetch recent orders
$recent_orders = $conn->query("SELECT o.order_id, o.order_date, o.total_price, o.payment_status, u.name 
                               FROM Orders o 
                               JOIN Users u ON o.user_id = u.user_id 
                               ORDER BY o.order_date DESC 
                               LIMIT 10");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Sundar Swadesh</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }

        .admin-navbar {
            background: linear-gradient(90deg, #667eea, #764ba2);
            padding: 20px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .admin-navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-navbar h2 {
            color: white;
            margin: 0;
            font-weight: 700;
        }

        .admin-navbar .nav-links a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            margin-left: 10px;
            font-weight: 500;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.1);
        }

        .admin-navbar .nav-links a:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .action-btn {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-decoration: none;
            color: #2c3e50;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            text-align: center;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            color: #667eea;
        }

        .action-btn i {
            display: block;
            font-size: 2.5rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .recent-orders {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .recent-orders h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .table {
            margin: 0;
        }

        .table thead {
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .admin-navbar .container {
                flex-direction: column;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <!-- Admin Navbar -->
    <div class="admin-navbar">
        <div class="container">
            <h2><i class="fas fa-shield-alt"></i> Admin Dashboard</h2>
            <div class="nav-links">
                <span style="color: white; margin-right: 15px;">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </span>
                <a href="books.php"><i class="fas fa-home"></i> View Site</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-book"></i></div>
                <div class="stat-value"><?php echo $total_books; ?></div>
                <div class="stat-label">Total Books</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Customers</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-value"><?php echo $total_orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-value">$<?php echo number_format($total_revenue, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="bookadd.php" class="action-btn">
                <i class="fas fa-plus-circle"></i>
                Add New Book
            </a>

            <a href="manage_books.php" class="action-btn">
                <i class="fas fa-edit"></i>
                Manage Books
            </a>

            <a href="manage_users.php" class="action-btn">
                <i class="fas fa-users-cog"></i>
                Manage Users
            </a>

            <a href="manage_orders.php" class="action-btn">
                <i class="fas fa-box-open"></i>
                View All Orders
            </a>
        </div>

        <!-- Recent Orders -->
        <div class="recent-orders">
            <h3><i class="fas fa-clock"></i> Recent Orders</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                                    <td>
                                        <span class="badge <?php echo $order['payment_status'] == 'Paid' ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo $order['payment_status']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No orders yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>