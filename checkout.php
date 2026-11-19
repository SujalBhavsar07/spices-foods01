<?php
$pageTitle = "Checkout - Spices Foods";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/db.php';

requireUserLogin();
$userId = $_SESSION['user_id'];

$subtotal = getCartSubtotal($conn, $userId);
if ($subtotal <= 0) {
    redirect('cart.php');
}

// Check stock for all items in cart before proceeding with checkout
$stmtStockCheck = $conn->prepare("SELECT c.quantity, f.name, f.stock FROM cart c JOIN foods f ON c.food_id = f.id WHERE c.user_id = ?");
$stmtStockCheck->bind_param("i", $userId);
$stmtStockCheck->execute();
$stockItems = $stmtStockCheck->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtStockCheck->close();

foreach ($stockItems as $sItem) {
    if ($sItem['quantity'] > $sItem['stock']) {
        setFlash('danger', 'Item "' . htmlspecialchars($sItem['name']) . '" exceeds available stock (' . $sItem['stock'] . ' available). Please adjust your cart.');
        redirect('cart.php');
    }
}

$deliveryCharge = 40.00;
$grandTotal = $subtotal + $deliveryCharge;

// Fetch User Default Info
$stmt = $conn->prepare("SELECT name, phone, address FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!-- Aesthetic Light Checkout Custom CSS -->
<style>
    .checkout-card {
        background-color: #FFFFFF !important;
        border: 1px solid var(--aesthetic-red-border, #F3D2D2) !important;
        box-shadow: 0 8px 24px rgba(139, 0, 0, 0.04) !important;
        border-radius: 16px !important;
    }

    .checkout-title {
        color: var(--aesthetic-red-primary, #8B0000) !important;
    }

    .checkout-section-title {
        color: var(--aesthetic-red-primary, #8B0000) !important;
        border-bottom: 2px solid var(--aesthetic-red-light, #FDF2F2);
        padding-bottom: 0.5rem;
    }

    .checkout-form .form-control {
        border: 1px solid var(--aesthetic-red-border, #F3D2D2);
        border-radius: 8px;
        padding: 0.65rem 0.9rem;
    }

    .checkout-form .form-control:focus {
        border-color: var(--aesthetic-red-accent, #C41E3A);
        box-shadow: 0 0 0 0.2rem rgba(196, 30, 58, 0.15);
    }

    .summary-box {
        background-color: var(--aesthetic-red-light, #FDF2F2) !important;
        border: 1px solid var(--aesthetic-red-border, #F3D2D2) !important;
        border-radius: 12px !important;
    }

    .grand-total-price {
        color: var(--aesthetic-red-primary, #8B0000) !important;
    }
</style>

<div class="container my-5">
    <h2 class="mb-4 fw-bold checkout-title">
        <i class="fa-solid fa-credit-card me-2"></i>Checkout Details
    </h2>
    
    <form action="place-order.php" method="POST" class="checkout-form">
        <div class="row g-4">
            <!-- Left Side: Form Information -->
            <div class="col-md-7">
                <div class="card p-4 checkout-card">
                    <h4 class="mb-4 fw-bold checkout-section-title">Delivery Information</h4>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Recipient Name *</label>
                        <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Phone Number *</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Delivery Address *</label>
                        <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Payment Method</label>
                        <input type="text" class="form-control bg-light" value="Cash on Delivery" readonly>
                    </div>
                </div>
            </div>

            <!-- Right Side: Order Summary -->
            <div class="col-md-5">
                <div class="card p-4 checkout-card">
                    <h4 class="mb-4 fw-bold checkout-section-title">Order Summary</h4>
                    <div class="d-flex justify-content-between mb-2 text-secondary">
                        <span>Items Subtotal</span>
                        <span class="fw-semibold text-dark"><?= formatPrice($subtotal) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-secondary">
                        <span>Delivery Charge</span>
                        <span class="fw-semibold text-dark"><?= formatPrice($deliveryCharge) ?></span>
                    </div>
                    
                    <hr style="border-color: var(--aesthetic-red-border, #F3D2D2);">
                    
                    <div class="summary-box p-3 my-3">
                        <div class="d-flex justify-content-between align-items-center fw-bold fs-5">
                            <span class="text-dark">Grand Total</span>
                            <span class="grand-total-price fs-4"><?= formatPrice($grandTotal) ?></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-aesthetic-red w-100 btn-lg fw-bold py-3 mt-2">
                        <i class="fa-solid fa-check-circle me-2"></i>Place Order Now
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>