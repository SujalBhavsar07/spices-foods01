<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit(); }

// Toggle User Status
if (isset($_GET['toggle'])) {
    $usr_id = (int)$_GET['toggle'];
    $stmt = $conn->prepare("UPDATE users SET is_active = IF(is_active=1, 0, 1) WHERE id = ?");
    $stmt->bind_param("i", $usr_id);
    $stmt->execute();
    $stmt->close();
    header("Location: users.php");
    exit();
}

// Delete User
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $stmt->close();
    header("Location: users.php");
    exit();
}

$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="bg-dark text-white p-3 vh-100 position-fixed" style="width: 250px;">
            <!-- Changed text-warning to text-danger -->
            <h4 class="fw-bold text-danger mb-4 px-2">Admin Panel</h4>
            <ul class="nav nav-pills flex-column mb-auto gap-1">
                <li><a href="dashboard.php" class="nav-link text-white"><i class="fa-solid fa-chart-line me-2"></i>Dashboard</a></li>
                <li><a href="category.php" class="nav-link text-white"><i class="fa-solid fa-list me-2"></i>Categories</a></li>
                <li><a href="subcategory.php" class="nav-link text-white"><i class="fa-solid fa-tags me-2"></i>Subcategories</a></li>
                <li><a href="food.php" class="nav-link text-white"><i class="fa-solid fa-utensils me-2"></i>Foods</a></li>
                <!-- Changed active bg-warning to bg-danger -->
                <li><a href="users.php" class="nav-link active bg-danger text-white fw-bold"><i class="fa-solid fa-users me-2"></i>Users</a></li>
                <li><a href="orders.php" class="nav-link text-white"><i class="fa-solid fa-receipt me-2"></i>Orders</a></li>
                <li class="mt-4"><a href="logout.php" class="nav-link text-danger fw-semibold"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="p-4 w-100" style="margin-left: 250px;">
            <!-- Heading styled in red -->
            <h3 class="fw-bold mb-4 text-danger">Customer Accounts</h3>

            <div class="card border-0 shadow-sm rounded-4 p-3">
                <!-- Changed table-light to table-danger -->
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-danger">
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($users && $users->num_rows > 0): ?>
                            <?php while ($usr = $users->fetch_assoc()): ?>
                                <tr>
                                    <!-- User ID styled in red -->
                                    <td class="fw-bold text-danger">#<?= (int)$usr['id'] ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($usr['name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($usr['email'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($usr['phone'] ?? '') ?></td>
                                    <td>
                                        <?php if (!empty($usr['is_active'])): ?>
                                            <!-- Active badge red theme -->
                                            <span class="badge bg-danger">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-dark">Blocked</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Changed btn-outline-warning to btn-outline-danger -->
                                        <a href="users.php?toggle=<?= (int)$usr['id'] ?>" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-power-off"></i> Toggle Status
                                        </a>
                                        <a href="users.php?delete=<?= (int)$usr['id'] ?>" onclick="return confirm('Delete user account?');" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No registered users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>