<?php
// Calculate total cart items count dynamically for logged-in user
$cart_count = 0;
if (isUserLoggedIn() && isset($conn)) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $cart_count = $result['total'] ?? 0;
    $stmt->close();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-3">
    <div class="container">
        <!-- Brand Logo / Name -->
        <!-- Changed text-warning to text-danger -->
        <a class="navbar-brand text-danger d-flex align-items-center gap-2" href="index.php">
            <i class="fa-solid fa-utensils fs-4"></i>
            <span>Spices Foods</span>
            <span class="badge bg-success small ms-1 fs-6 fw-normal" style="font-size: 10px !important;">Pure Veg</span>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-2">
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="shop.php">Menu / Shop</a>
                </li>
            </ul>

            <!-- Right Side Cart & User Actions -->
            <div class="d-flex align-items-center gap-3">
                <!-- Cart Icon Button -->
                <!-- Changed btn-outline-warning to btn-outline-danger -->
                <a href="cart.php" class="btn btn-outline-danger position-relative px-3 rounded-pill fw-semibold">
                    <i class="fa-solid fa-cart-shopping me-1"></i> Cart
                    <?php if ($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $cart_count ?>
                        </span>
                    <?php endif; ?>
                </a>

                <?php if (isUserLoggedIn()): ?>
                    <!-- Logged In User Dropdown -->
                    <!-- Changed btn-warning to btn-danger -->
                    <div class="dropdown">
                        <button class="btn btn-danger dropdown-toggle fw-bold rounded-pill px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-circle-user me-1"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Account') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                            <!-- Changed icon text-warning to text-danger -->
                            <li><a class="dropdown-item py-2" href="my-orders.php"><i class="fa-solid fa-box-archive me-2 text-danger"></i>My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger fw-semibold" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Guest Links -->
                    <a href="login.php" class="btn btn-outline-light rounded-pill px-3 fw-medium">Login</a>
                    <!-- Changed btn-warning to btn-danger -->
                    <a href="register.php" class="btn btn-danger rounded-pill px-3 fw-bold">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>


<!-- Global Flash Alert Messages -->
<?php if (isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show mb-0 text-center rounded-0 border-0" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($_SESSION['success_msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_msg']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_msg'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-0 text-center rounded-0 border-0" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i> <?= htmlspecialchars($_SESSION['error_msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>