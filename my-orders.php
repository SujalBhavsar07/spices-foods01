<?php
$pageTitle = "Order History - Spices Foods";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/db.php';

requireUserLogin();
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$orders = $stmt->get_result();
?>

<div class="container my-5">
    <!-- Changed heading to red -->
    <h2 class="text-danger fw-bold">Your Order History</h2>
    
    <?php if ($orders->num_rows > 0): ?>
        <div class="table-responsive mt-3">
            <table class="table table-hover align-middle">
                <!-- Changed table-dark to table-danger -->
                <thead class="table-danger">
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($o = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $o['id'] ?></td>
                            <td><?= formatDate($o['order_date']) ?></td>
                            <td><?= formatPrice($o['total_amount']) ?></td>
                            <!-- Changed badge color to red -->
                            <td><span class="badge bg-danger"><?= $o['payment_status'] ?></span></td>
                            <td><?= getStatusBadge($o['status']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <!-- Changed alert-info to alert-danger -->
        <div class="alert alert-danger mt-3">You have not placed any orders yet.</div>
    <?php endif; ?>
</div>


<?php require_once 'includes/footer.php'; ?>