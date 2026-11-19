<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';

requireUserLogin();
$userId = $_SESSION['user_id'];

// Handle Actions (Add, Update, Remove)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $foodId = (int)($_POST['food_id'] ?? 0);

    if ($action === 'add') {
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        
        // Fetch current stock and cart quantity
        $stCheck = $conn->prepare("SELECT f.name, f.stock, COALESCE(c.quantity, 0) as current_cart_qty 
                                   FROM foods f 
                                   LEFT JOIN cart c ON f.id = c.food_id AND c.user_id = ? 
                                   WHERE f.id = ?");
        $stCheck->bind_param("ii", $userId, $foodId);
        $stCheck->execute();
        $foodStockInfo = $stCheck->get_result()->fetch_assoc();
        $stCheck->close();

        if ($foodStockInfo) {
            $totalDesired = $foodStockInfo['current_cart_qty'] + $qty;
            if ($totalDesired > $foodStockInfo['stock']) {
                setFlash('danger', 'Cannot add item. Only ' . $foodStockInfo['stock'] . ' units available in stock for "' . htmlspecialchars($foodStockInfo['name']) . '".');
                redirect('cart.php');
            }
        }
        
        // Insert or update on key conflict
        $stmt = $conn->prepare("INSERT INTO cart (user_id, food_id, quantity) VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE quantity = quantity + ?");
        $stmt->bind_param("iiii", $userId, $foodId, $qty, $qty);
        $stmt->execute();
        $stmt->close();
        setFlash('success', 'Item added to cart.');
    } 
    elseif ($action === 'update') {
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        $stCheck = $conn->prepare("SELECT name, stock FROM foods WHERE id = ?");
        $stCheck->bind_param("i", $foodId);
        $stCheck->execute();
        $foodStockInfo = $stCheck->get_result()->fetch_assoc();
        $stCheck->close();

        if ($foodStockInfo && $qty > $foodStockInfo['stock']) {
            setFlash('danger', 'Quantity requested exceeds available stock (' . $foodStockInfo['stock'] . ' available) for "' . htmlspecialchars($foodStockInfo['name']) . '".');
            redirect('cart.php');
        }

        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND food_id = ?");
        $stmt->bind_param("iii", $qty, $userId, $foodId);
        $stmt->execute();
        $stmt->close();
    } 
    elseif ($action === 'remove') {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND food_id = ?");
        $stmt->bind_param("ii", $userId, $foodId);
        $stmt->execute();
        $stmt->close();
        setFlash('info', 'Item removed from cart.');
    }
    redirect('cart.php');
}

// Fetch Cart Data
$stmt = $conn->prepare("SELECT c.quantity, f.id as food_id, f.name, f.price, f.image, f.stock 
                        FROM cart c 
                        JOIN foods f ON c.food_id = f.id 
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartItems = $stmt->get_result();

$pageTitle = "Shopping Cart - Spices Foods";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<!-- Bright Aesthetic Red Cart Styling -->
<style>
    .cart-card {
        background-color: #FFFFFF !important;
        border: 1px solid var(--aesthetic-red-border, #F3D2D2) !important;
        box-shadow: 0 8px 24px rgba(139, 0, 0, 0.04) !important;
        border-radius: 16px !important;
        padding: 2rem;
    }

    .cart-title {
        color: var(--aesthetic-red-primary, #8B0000) !important;
    }

    .cart-table thead {
        background-color: var(--aesthetic-red-light, #FDF2F2) !important;
        color: var(--aesthetic-red-primary, #8B0000) !important;
        border-bottom: 2px solid var(--aesthetic-red-border, #F3D2D2) !important;
    }

    .cart-table th {
        font-weight: 600;
        padding: 1rem 0.75rem;
    }

    .cart-table td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    .cart-table tbody tr {
        border-bottom: 1px solid #F3F4F6;
        transition: background-color 0.2s ease;
    }

    .cart-table tbody tr:hover {
        background-color: #FAFAFA;
    }

    .qty-input {
        border: 1px solid var(--aesthetic-red-border, #F3D2D2) !important;
        border-radius: 8px !important;
        text-align: center;
    }

    .qty-input:focus {
        border-color: var(--aesthetic-red-accent, #C41E3A) !important;
        box-shadow: 0 0 0 0.2rem rgba(196, 30, 58, 0.15) !important;
    }

    .btn-qty-save {
        border-color: var(--aesthetic-red-border, #F3D2D2) !important;
        color: var(--aesthetic-red-accent, #C41E3A) !important;
        background-color: #FFFFFF;
        transition: all 0.2s ease;
    }

    .btn-qty-save:hover {
        background-color: var(--aesthetic-red-light, #FDF2F2) !important;
        color: var(--aesthetic-red-primary, #8B0000) !important;
    }

    .btn-remove-item {
        background-color: #FDF2F2 !important;
        color: #C41E3A !important;
        border: 1px solid #F8B4B4 !important;
        transition: all 0.2s ease;
    }

    .btn-remove-item:hover {
        background-color: #C41E3A !important;
        color: #FFFFFF !important;
    }

    .cart-summary-box {
        background-color: var(--aesthetic-red-light, #FDF2F2);
        border: 1px solid var(--aesthetic-red-border, #F3D2D2);
        border-radius: 12px;
        padding: 1.25rem 1.75rem;
    }

    .total-price {
        color: var(--aesthetic-red-primary, #8B0000) !important;
        font-weight: 700;
    }

    .empty-cart-alert {
        background-color: #FFFFFF !important;
        border: 1px solid var(--aesthetic-red-border, #F3D2D2) !important;
        color: var(--aesthetic-text-main, #1F2937) !important;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
    }

    .empty-cart-alert a {
        color: var(--aesthetic-red-accent, #C41E3A);
        font-weight: 600;
        text-decoration: none;
    }

    .empty-cart-alert a:hover {
        text-decoration: underline;
    }
</style>

<div class="container my-5">
    <div class="cart-card">
        <h2 class="fw-bold cart-title mb-4">
            <i class="fa-solid fa-cart-shopping me-2"></i>Your Shopping Cart
        </h2>

        <?php if ($cartItems->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table cart-table align-middle">
                    <thead>
                        <tr>
                            <th>Item Details</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        while ($item = $cartItems->fetch_assoc()): 
                            $total = $item['price'] * $item['quantity'];
                            $subtotal += $total;
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="uploads/foods/<?= htmlspecialchars($item['image']) ?>" width="55" height="55" class="rounded-3 me-3" style="object-fit: cover; border: 1px solid #E5E7EB;" onerror="this.src='uploads/foods/default_food.jpg';">
                                        <div>
                                            <span class="fw-semibold text-dark d-block"><?= htmlspecialchars($item['name']) ?></span>
                                            <?php if ($item['quantity'] > $item['stock']): ?>
                                                <span class="badge bg-danger small mt-1">Exceeds stock (<?= $item['stock'] ?> available)</span>
                                            <?php elseif ($item['stock'] <= 5): ?>
                                                <span class="badge bg-warning text-dark small mt-1">Only <?= $item['stock'] ?> left!</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="fw-medium text-secondary"><?= formatPrice($item['price']) ?></td>
                                <td>
                                    <form action="cart.php" method="POST" class="d-flex align-items-center gap-2">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="food_id" value="<?= $item['food_id'] ?>">
                                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="form-control form-control-sm qty-input" style="width: 70px;">
                                        <button type="submit" class="btn btn-sm btn-qty-save fw-medium">Save</button>
                                    </form>
                                </td>
                                <td class="fw-bold text-dark"><?= formatPrice($total) ?></td>
                                <td class="text-center">
                                    <form action="cart.php" method="POST">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="food_id" value="<?= $item['food_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-remove-item rounded-circle" title="Remove Item">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Cart Summary & Checkout -->
            <div class="cart-summary-box d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3">
                <div class="fs-4 fw-semibold text-dark mb-0">
                    Subtotal: <span class="total-price ms-2"><?= formatPrice($subtotal) ?></span>
                </div>
                <div class="d-flex gap-2">
                    <a href="shop.php" class="btn btn-outline-secondary px-4 fw-medium">Continue Shopping</a>
                    <a href="checkout.php" class="btn btn-aesthetic-red btn-lg px-4 fw-bold">
                        Proceed to Checkout <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

        <?php else: ?>
            <div class="empty-cart-alert">
                <i class="fa-solid fa-basket-shopping fs-1 text-danger mb-3 d-block"></i>
                <h5 class="fw-bold mb-2">Your cart is currently empty</h5>
                <p class="text-muted mb-3">Looks like you haven't added any items to your cart yet.</p>
                <a href="shop.php" class="btn btn-aesthetic-red px-4 py-2">
                    <i class="fa-solid fa-utensils me-2"></i>Browse Menu
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>