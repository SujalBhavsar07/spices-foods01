<?php
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminLogin();

/* =========================================================
   UPDATE ORDER & PAYMENT STATUS
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {

    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'] ?? 'Pending';
    $payment_status = $_POST['payment_status'] ?? 'Pending';

    // Whitelist status values for safety
    $allowed_statuses = ['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
    $allowed_payment = ['Pending', 'Paid'];

    if (!in_array($status, $allowed_statuses, true)) {
        $status = 'Pending';
    }
    if (!in_array($payment_status, $allowed_payment, true)) {
        $payment_status = 'Pending';
    }

    $stmt = $conn->prepare("UPDATE orders SET status = ?, payment_status = ? WHERE id = ?");

    if ($stmt) {
        $stmt->bind_param("ssi", $status, $payment_status, $order_id);

        if ($stmt->execute()) {
            setFlash('success', "Order #{$order_id} updated successfully!");
        } else {
            setFlash('danger', 'Failed to update order status.');
        }
        $stmt->close();
    }

    redirect('admin/orders.php');
}

/* =========================================================
   FETCH ALL ORDERS
========================================================= */
$query = "
    SELECT 
        o.*,
        COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
    ORDER BY o.id DESC
";

$orders_res = $conn->query($query);
$orders = [];
if ($orders_res && $orders_res->num_rows > 0) {
    while ($row = $orders_res->fetch_assoc()) {
        $orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Spices Foods Admin</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { background: #f8f9fa; }
        .sidebar { width: 250px; min-height: 100vh; background: #121212; border-right: 1px solid #1f1f1f; }
        .sidebar .nav-link { color: #b0b0b0; border-radius: 8px; transition: all 0.25s ease; border-left: 3px solid transparent; }
        .sidebar .nav-link:hover { background: #1e1e1e; color: #ffffff; border-left: 3px solid #dc3545; padding-left: 18px; }
        .sidebar .nav-link.active { background: #dc3545 !important; color: #ffffff !important; font-weight: 600; border-left: 3px solid #ff6b6b; }
        .main-content { margin-left: 250px; }
        .card { border-radius: 12px; }
        .table-dark { background: #1a1a1a !important; color: #ffffff !important; }
        .btn-danger { border-radius: 30px; font-weight: 600; background-color: #dc3545; border-color: #dc3545; }
        .order-food-image { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6; flex-shrink: 0; }
        .food-item { min-width: 360px; padding: 8px 0; border-bottom: 1px solid #eeeeee; }
        .food-item:last-child { border-bottom: none; }
        .food-name { font-weight: 600; color: #212529; margin-bottom: 2px; }
        .food-info { font-size: 13px; color: #6c757d; }
        .food-total { font-weight: 700; color: #198754; white-space: nowrap; }

        @media(max-width:768px) {
            .sidebar { width: 100%; position: relative !important; min-height: auto; }
            .main-content { margin-left: 0; }
            .food-item { min-width: 300px; }
        }
    </style>
</head>
<body>

<div class="d-flex flex-column flex-md-row">

    <!-- SIDEBAR -->
    <div class="sidebar p-3 position-fixed z-3">
        <h4 class="fw-bold text-danger mb-4 px-2 d-flex align-items-center gap-2">
            <i class="fa-solid fa-utensils"></i> Admin Panel
        </h4>
        <ul class="nav nav-pills flex-column mb-auto gap-1">
            <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line me-2"></i>Dashboard</a></li>
            <li><a href="category.php" class="nav-link"><i class="fa-solid fa-list me-2"></i>Categories</a></li>
            <li><a href="subcategory.php" class="nav-link"><i class="fa-solid fa-tags me-2"></i>Subcategories</a></li>
            <li><a href="food.php" class="nav-link"><i class="fa-solid fa-utensils me-2"></i>Food Items</a></li>
            <li><a href="users.php" class="nav-link"><i class="fa-solid fa-users me-2"></i>Users</a></li>
            <li><a href="orders.php" class="nav-link active"><i class="fa-solid fa-receipt me-2"></i>Orders</a></li>
            <li class="mt-4"><a href="logout.php" class="nav-link text-danger fw-semibold"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="p-4 w-100 main-content">

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark mb-1">Customer Orders</h3>
                <p class="text-muted small mb-0">View ordered food items, customer details, delivery status, and payment information.</p>
            </div>
            <a href="orders.php" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-rotate-right me-1"></i> Refresh
            </a>
        </div>

        <?= displayFlash() ?>

        <!-- ORDERS TABLE -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Order ID</th>
                            <th>Customer & Contact</th>
                            <th>Ordered Food</th>
                            <th>Order Total</th>
                            <th>Payment</th>
                            <th>Order Status</th>
                            <th class="text-end px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php 
                            // Prepare reusable query for order items
                            $item_stmt = $conn->prepare("
                                SELECT oi.food_name, oi.quantity, oi.price, f.image
                                FROM order_items oi
                                LEFT JOIN foods f ON oi.food_id = f.id
                                WHERE oi.order_id = ?
                            ");
                            ?>
                            <?php foreach ($orders as $ord): ?>
                                <tr>
                                    <!-- ORDER ID -->
                                    <td class="fw-bold text-primary">
                                        #<?= (int)$ord['id'] ?>
                                    </td>

                                    <!-- CUSTOMER -->
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($ord['customer_name'] ?? 'N/A') ?></div>
                                        <div class="small text-muted">
                                            <i class="fa-solid fa-phone text-secondary me-1"></i><?= htmlspecialchars($ord['phone'] ?? 'N/A') ?>
                                        </div>
                                        <div class="small text-muted text-truncate" style="max-width:200px;">
                                            <i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($ord['address'] ?? 'N/A') ?>
                                        </div>
                                    </td>

                                    <!-- ORDERED FOOD -->
                                    <td>
                                        <?php
                                        if ($item_stmt) {
                                            $item_stmt->bind_param("i", $ord['id']);
                                            $item_stmt->execute();
                                            $items_res = $item_stmt->get_result();

                                            if ($items_res && $items_res->num_rows > 0) {
                                                while ($item = $items_res->fetch_assoc()) {
                                                    $quantity = (int)$item['quantity'];
                                                    $price = (float)$item['price'];
                                                    $total_price = $price * $quantity;
                                                    $image = !empty($item['image']) ? '../uploads/foods/' . $item['image'] : '../uploads/foods/default_food.jpg';
                                                    ?>
                                                    <div class="food-item d-flex align-items-center gap-3">
                                                        <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($item['food_name']) ?>" class="order-food-image" onerror="this.src='../uploads/foods/default_food.jpg';">
                                                        <div class="flex-grow-1">
                                                            <div class="food-name"><?= htmlspecialchars($item['food_name']) ?></div>
                                                            <div class="food-info">Quantity: <span class="badge bg-secondary"><?= $quantity ?></span></div>
                                                            <div class="food-info">Price: <strong class="text-dark"><?= formatPrice($price) ?></strong></div>
                                                        </div>
                                                        <div class="text-end">
                                                            <div class="small text-muted">Total</div>
                                                            <div class="food-total"><?= formatPrice($total_price) ?></div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                            } else {
                                                echo '<span class="text-muted small">No items found</span>';
                                            }
                                        }
                                        ?>
                                    </td>

                                    <!-- ORDER TOTAL -->
                                    <td class="fw-bold text-success">
                                        <?= formatPrice($ord['total_amount'] ?? 0) ?>
                                    </td>

                                    <!-- PAYMENT -->
                                    <td>
                                        <?php if (($ord['payment_status'] ?? '') === 'Paid'): ?>
                                            <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i>Paid</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="fa-solid fa-clock me-1"></i>Unpaid</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- ORDER STATUS -->
                                    <td>
                                        <?= getStatusBadge($ord['status'] ?? 'Pending') ?>
                                    </td>

                                    <!-- ACTIONS -->
                                    <td class="text-end px-4">
                                        <button class="btn btn-sm btn-dark rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#orderModal<?= (int)$ord['id'] ?>">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>Manage
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($item_stmt) $item_stmt->close(); ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-box-open fa-3x mb-3 text-secondary"></i>
                                    <p class="mb-0">No customer orders found in the database.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- =================================================
     MODAL DIALOGS (Moved outside the table layout)
================================================== -->
<?php if (!empty($orders)): ?>
    <?php 
    $modal_stmt = $conn->prepare("SELECT food_name, quantity, price FROM order_items WHERE order_id = ?");
    ?>
    <?php foreach ($orders as $ord): ?>
        <div class="modal fade" id="orderModal<?= (int)$ord['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <form method="POST" action="orders.php">
                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title fw-bold">
                                <i class="fa-solid fa-boxes-packing text-warning me-2"></i>
                                Manage Order #<?= (int)$ord['id'] ?>
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-4">
                            <input type="hidden" name="order_id" value="<?= (int)$ord['id'] ?>">

                            <!-- DELIVERY STATUS -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Order Delivery Status</label>
                                <select name="status" class="form-select border-secondary">
                                    <?php
                                    $statuses = ['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Delivered', 'Cancelled'];
                                    foreach ($statuses as $st):
                                    ?>
                                        <option value="<?= htmlspecialchars($st) ?>" <?= ($ord['status'] === $st) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($st) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- PAYMENT STATUS -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Payment Status</label>
                                <select name="payment_status" class="form-select border-secondary">
                                    <option value="Pending" <?= ($ord['payment_status'] === 'Pending') ? 'selected' : '' ?>>Pending (Unpaid)</option>
                                    <option value="Paid" <?= ($ord['payment_status'] === 'Paid') ? 'selected' : '' ?>>Paid</option>
                                </select>
                            </div>

                            <!-- ORDER ITEMS SUMMARY -->
                            <div class="card bg-light border-0 p-3 mt-3">
                                <h6 class="fw-bold mb-3 text-dark small"><i class="fa-solid fa-list-check me-1"></i>Order Items</h6>
                                <ul class="list-unstyled mb-0 small">
                                    <?php
                                    if ($modal_stmt) {
                                        $modal_stmt->bind_param("i", $ord['id']);
                                        $modal_stmt->execute();
                                        $modal_res = $modal_stmt->get_result();

                                        while ($item = $modal_res->fetch_assoc()) {
                                            $item_total = $item['price'] * $item['quantity'];
                                            ?>
                                            <li class="d-flex justify-content-between py-2 border-bottom">
                                                <span>
                                                    <strong><?= (int)$item['quantity'] ?>x</strong> <?= htmlspecialchars($item['food_name']) ?><br>
                                                    <small class="text-muted"><?= formatPrice($item['price']) ?> each</small>
                                                </span>
                                                <span class="fw-semibold text-success"><?= formatPrice($item_total) ?></span>
                                            </li>
                                            <?php
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_order" class="btn btn-danger fw-bold rounded-pill px-4">
                                <i class="fa-solid fa-floppy-disk me-1"></i>Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if ($modal_stmt) $modal_stmt->close(); ?>
<?php endif; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>