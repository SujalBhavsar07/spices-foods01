<?php
require_once 'includes/db.php';
// Fetch current cart count if user is logged in
$cartCount = 0;
if (isUserLoggedIn()) {
    $cartCount = getCartCount($conn, $_SESSION['user_id']);
}

// Fetch categories for the dropdown menu
$catQuery = "SELECT id, name FROM categories ORDER BY name ASC LIMIT 6";
$catResult = $conn->query($catQuery);
?>

<!-- Aesthetic Red Custom CSS Scope -->
<style>
    /* Reset Top Spacing Global Overrides */
    html, body {
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Dark Aesthetic Red Color Palette */
    :root {
        --aesthetic-red-dark: #120708;       /* Very dark red-tinted background */
        --aesthetic-red-surface: #1E0C0E;    /* Dark crimson container fill */
        --aesthetic-red-primary: #8B0000;    /* Deep Crimson Primary */
        --aesthetic-red-accent: #C41E3A;     /* Vivid Rose Red Accent */
        --aesthetic-red-glow: #E32636;       /* Warm Coral Red Glow */
        --aesthetic-red-soft: #FADBD8;       /* Soft Light Red Tint */
    }

    /* Seamless Top Navbar Alignment */
    .aesthetic-red-navbar {
        background-color: var(--aesthetic-red-dark) !important;
        border-bottom: 1px solid rgba(196, 30, 58, 0.2);
        top: 0;
        margin-top: 0 !important;
    }

    .brand-accent {
        color: var(--aesthetic-red-glow) !important;
        transition: color 0.3s ease;
    }

    .brand-accent:hover {
        color: var(--aesthetic-red-soft) !important;
    }

    /* Dynamic Button Variants */
    .btn-aesthetic-red {
        background-color: var(--aesthetic-red-accent) !important;
        border-color: var(--aesthetic-red-accent) !important;
        color: #ffffff !important;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-aesthetic-red:hover {
        background-color: var(--aesthetic-red-glow) !important;
        border-color: var(--aesthetic-red-glow) !important;
        box-shadow: 0 0 12px rgba(227, 38, 54, 0.4);
    }

    .btn-outline-aesthetic-red {
        border-color: var(--aesthetic-red-accent) !important;
        color: var(--aesthetic-red-soft) !important;
        transition: all 0.3s ease;
    }

    .btn-outline-aesthetic-red:hover {
        background-color: var(--aesthetic-red-accent) !important;
        color: #ffffff !important;
        box-shadow: 0 0 10px rgba(196, 30, 58, 0.3);
    }

    /* Cart Count Badge */
    .badge-aesthetic-red {
        background-color: var(--aesthetic-red-glow) !important;
        color: #ffffff;
    }

    /* Dropdown Customizations */
    .dropdown-menu-dark-red {
        background-color: var(--aesthetic-red-surface) !important;
        border: 1px solid rgba(196, 30, 58, 0.25) !important;
    }

    .dropdown-menu-dark-red .dropdown-item {
        color: #E0E0E0 !important;
        transition: all 0.2s ease;
    }

    .dropdown-menu-dark-red .dropdown-item:hover {
        background-color: var(--aesthetic-red-primary) !important;
        color: #ffffff !important;
    }

    .dropdown-menu-dark-red .dropdown-divider {
        border-color: rgba(196, 30, 58, 0.2) !important;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark aesthetic-red-navbar sticky-top shadow-sm">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand fw-bold brand-accent fs-3" href="index.php">
            <i class="fa-solid fa-pepper-hot me-1"></i>Spices Foods
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="shop.php">Menu / Shop</a>
                </li>

                <!-- Categories Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Categories
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark-red shadow">
                        <?php if ($catResult && $catResult->num_rows > 0): ?>
                            <?php while ($cat = $catResult->fetch_assoc()): ?>
                                <li>
                                    <a class="dropdown-item" href="category.php?id=<?= $cat['id'] ?>">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li><a class="dropdown-item disabled text-muted" href="#">No Categories</a></li>
                        <?php endif; ?>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="about.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact</a>
                </li>
            </ul>

            <!-- Right Side (Cart & User Account) -->
            <div class="d-flex align-items-center gap-3">
                <!-- Cart Button -->
                <a href="cart.php" class="btn btn-outline-aesthetic-red position-relative">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill badge-aesthetic-red">
                            <?= $cartCount ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- User Profile / Login -->
                <?php if (isUserLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="btn btn-aesthetic-red dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-user me-1"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Account') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark-red shadow">
                            <li><a class="dropdown-item" href="profile.php"><i class="fa-solid fa-id-card me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="my-orders.php"><i class="fa-solid fa-bag-shopping me-2"></i>My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light me-2">Login</a>
                    <a href="register.php" class="btn btn-aesthetic-red">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>