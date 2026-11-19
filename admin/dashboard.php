<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Dashboard Queries
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'Paid'")->fetch_assoc()['total'] ?? 0;
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_foods = $conn->query("SELECT COUNT(*) as count FROM foods")->fetch_assoc()['count'];

// Recent Orders Query
$recent_orders = $conn->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Spices Foods</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { width: 250px; min-height: 100vh; background-color: #212529; position: fixed; }
        .main-content { margin-left: 250px; }
        .stat-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(220, 53, 69, 0.1);
        }
        .chart-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
            background: #ffffff;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar Navigation -->
        <div class="sidebar text-white p-3">
            <h4 class="fw-bold text-danger mb-4 px-2 d-flex align-items-center gap-2">
                <i class="fa-solid fa-utensils"></i> Admin Panel
            </h4>
            <ul class="nav nav-pills flex-column mb-auto gap-1">
                <li class="nav-item"><a href="dashboard.php" class="nav-link active bg-danger text-white fw-bold"><i class="fa-solid fa-chart-line me-2"></i>Dashboard</a></li>
                <li class="nav-item"><a href="category.php" class="nav-link text-white"><i class="fa-solid fa-list me-2"></i>Categories</a></li>
                <li class="nav-item"><a href="subcategory.php" class="nav-link text-white"><i class="fa-solid fa-tags me-2"></i>Subcategories</a></li>
                <li class="nav-item"><a href="food.php" class="nav-link text-white"><i class="fa-solid fa-utensils me-2"></i>Foods</a></li>
                <li class="nav-item"><a href="users.php" class="nav-link text-white"><i class="fa-solid fa-users me-2"></i>Users</a></li>
                <li class="nav-item"><a href="orders.php" class="nav-link text-white"><i class="fa-solid fa-receipt me-2"></i>Orders</a></li>
                <li class="nav-item mt-4"><a href="logout.php" class="nav-link text-danger fw-semibold"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
            </ul>
        </div>

        <!-- Main Content Space -->
        <div class="p-4 w-100 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-0 text-danger">Dashboard Overview</h3>
                    <small class="text-muted">Store metrics and recent orders</small>
                </div>
                <span class="badge bg-white text-dark border shadow-sm p-2 fw-normal rounded-3">
                    <i class="fa-solid fa-user-shield me-1 text-danger"></i> Logged in as: <strong><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></strong>
                </span>
            </div>

            <!-- Stats Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-semibold d-block">Total Revenue</small>
                                <h3 class="fw-bold text-danger mb-0 mt-1">₹<?= number_format($total_revenue, 2) ?></h3>
                            </div>
                            <div class="rounded-circle bg-danger-subtle p-3 text-danger">
                                <i class="fa-solid fa-indian-rupee-sign fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-semibold d-block">Total Orders</small>
                                <h3 class="fw-bold text-danger mb-0 mt-1"><?= $total_orders ?></h3>
                            </div>
                            <div class="rounded-circle bg-danger-subtle p-3 text-danger">
                                <i class="fa-solid fa-basket-shopping fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-semibold d-block">Active Users</small>
                                <h3 class="fw-bold text-danger mb-0 mt-1"><?= $total_users ?></h3>
                            </div>
                            <div class="rounded-circle bg-danger-subtle p-3 text-danger">
                                <i class="fa-solid fa-users fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted fw-semibold d-block">Total Food Items</small>
                                <h3 class="fw-bold text-danger mb-0 mt-1"><?= $total_foods ?></h3>
                            </div>
                            <div class="rounded-circle bg-danger-subtle p-3 text-danger">
                                <i class="fa-solid fa-utensils fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card chart-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-danger mb-0">
                                <i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Orders
                            </h5>
                            <a href="orders.php" class="btn btn-sm btn-outline-danger rounded-pill px-3">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
                                        <?php while ($ord = $recent_orders->fetch_assoc()): ?>
                                            <tr>
                                                <td class="fw-bold text-danger">#<?= $ord['id'] ?></td>
                                                <td><?= htmlspecialchars($ord['customer_name']) ?></td>
                                                <td class="fw-bold">₹<?= number_format($ord['total_amount'], 2) ?></td>
                                                <td><?= getStatusBadge($ord['status']) ?></td>
                                                <td class="small text-muted"><?= date('d M Y, h:i A', strtotime($ord['order_date'])) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No recent orders found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>