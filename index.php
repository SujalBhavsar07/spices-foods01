<?php
$pageTitle = "Home - Spices Foods";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/db.php';

// 1. Fetch Categories along with their Subcategories
$categoriesQuery = "SELECT c.id AS cat_id, c.name AS cat_name, c.image AS cat_image, c.description AS cat_desc,
                           s.id AS sub_id, s.name AS sub_name
                    FROM categories c
                    LEFT JOIN subcategories s ON c.id = s.category_id
                    ORDER BY c.name ASC, s.name ASC";

$catResult = $conn->query($categoriesQuery);

// Organize Categories and nested Subcategories into a structured array
$categoriesMap = [];
if ($catResult && $catResult->num_rows > 0) {
    while ($row = $catResult->fetch_assoc()) {
        $catId = $row['cat_id'];
        if (!isset($categoriesMap[$catId])) {
            $categoriesMap[$catId] = [
                'id' => $row['cat_id'],
                'name' => $row['cat_name'],
                'image' => $row['cat_image'],
                'description' => $row['cat_desc'],
                'subcategories' => []
            ];
        }
        if ($row['sub_id']) {
            $categoriesMap[$catId]['subcategories'][] = [
                'id' => $row['sub_id'],
                'name' => $row['sub_name']
            ];
        }
    }
}

// 2. Fetch Featured / Popular Dishes
$featuredQuery = "SELECT f.*, c.name AS category_name, s.name AS subcategory_name 
                  FROM foods f 
                  JOIN subcategories s ON f.subcategory_id = s.id 
                  JOIN categories c ON s.category_id = c.id 
                  WHERE f.is_available = 1 
                  ORDER BY f.id DESC LIMIT 8";
$featured = $conn->query($featuredQuery);
?>

<!-- ================= HERO BANNER ================= -->
<div class="hero-banner text-light py-5 text-center shadow-sm position-relative overflow-hidden">
    <div class="hero-overlay"></div>
    <div class="container py-5 position-relative z-2">
        <span class="badge bg-brand-gold text-dark fw-bold mb-2 px-3 py-2 rounded-pill shadow-sm">Fresh & Fast Delivery</span>
        <h1 class="display-3 fw-bold text-white mb-3">Authentic Spices & Delicacies</h1>
        <p class="lead text-light opacity-90 col-md-8 mx-auto mb-4">
            Experience hand-crafted dishes infused with rich traditional spices, delivered piping hot to your doorstep.
        </p>

        <!-- Quick Search Bar -->
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form action="shop.php" method="GET" class="input-group input-group-lg shadow-sm">
                    <input type="text" name="search" class="form-control border-0" placeholder="Search for biryani, curries, spices..." required>
                    <button class="btn btn-brand fw-bold px-4" type="submit">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ================= CATEGORIES SECTION ================= -->
<div class="container my-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold text-dark mb-2 display-6">Explore Our Menu</h2>
        <p class="text-muted fs-5">Browse through our curated food categories</p>
    </div>

    <div class="row g-4 justify-content-center">
        <?php if (!empty($categoriesMap)): ?>
            <?php foreach ($categoriesMap as $cat): ?>
                <div class="col-12 col-md-6">
                    <div class="card h-100 border-0 shadow-sm rounded-4 hover-lift transition-all overflow-hidden bg-white">
                        <div class="card-body p-4 d-flex flex-column">
                            
                            <div class="d-flex align-items-center mb-3">
                                <?php 
                                    $catImg = !empty($cat['image']) && file_exists('uploads/category/' . $cat['image']) 
                                        ? 'uploads/category/' . htmlspecialchars($cat['image']) 
                                        : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="%23cccccc" class="bi bi-card-image" viewBox="0 0 16 16"><path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/><path d="M1.5 2A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13zm13 1a.5.5 0 0 1 .5.5v6l-3.775-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12v-8.5a.5.5 0 0 1 .5-.5h13z"/></svg>';
                                ?>
                                <div class="position-relative me-3 flex-shrink-0">
                                    <img src="<?= $catImg ?>" 
                                         alt="<?= htmlspecialchars($cat['name']) ?>" 
                                         class="rounded-circle border border-2 border-brand-subtle shadow-sm p-1" 
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="fw-bold text-dark mb-1">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </h4>
                                    <span class="badge bg-brand-light text-brand rounded-pill px-3 py-1 small fw-medium">
                                        Pure Veg Menu
                                    </span>
                                </div>
                            </div>

                            <?php if (!empty($cat['description'])): ?>
                                <p class="text-muted small mb-4 flex-grow-1 line-clamp-2">
                                    <?= htmlspecialchars($cat['description']) ?>
                                </p>
                            <?php else: ?>
                                <div class="flex-grow-1"></div>
                            <?php endif; ?>

                            <div class="pt-2 mt-auto">
                                <a href="category.php?id=<?= $cat['id'] ?>" class="btn btn-brand w-100 fw-bold py-2 rounded-3 shadow-sm text-uppercase tracking-wider">
                                    Explore <?= htmlspecialchars($cat['name']) ?> <i class="fa-solid fa-arrow-right ms-2"></i>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center py-4 rounded-3 shadow-sm">
                    <i class="fa-solid fa-info-circle me-2"></i> No categories found.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ================= FEATURED DISHES SECTION ================= -->
<div class="bg-white py-5 border-top">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Top Rated Dishes</h2>
                <p class="text-muted mb-0">Our most popular spicy delights loved by customers</p>
            </div>
            <a href="shop.php" class="btn btn-outline-brand fw-bold">View Full Menu <i class="fa-solid fa-chevron-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            <?php if ($featured && $featured->num_rows > 0): ?>
                <?php while ($food = $featured->fetch_assoc()): ?>
                    <?php 
                        $foodImg = !empty($food['image']) && file_exists('uploads/foods/' . $food['image']) 
                            ? 'uploads/foods/' . htmlspecialchars($food['image']) 
                            : 'https://via.placeholder.com/300x200?text=Food+Image';
                    ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm position-relative">
                            
                            <span class="position-absolute top-0 start-0 m-2 badge bg-dark opacity-75">
                                <?= htmlspecialchars($food['category_name'] ?? 'Special') ?>
                            </span>

                            <a href="food-details.php?id=<?= $food['id'] ?>">
                                <img src="<?= $foodImg ?>" class="card-img-top" alt="<?= htmlspecialchars($food['name']) ?>" style="height: 190px; object-fit: cover;">
                            </a>

                            <div class="card-body d-flex flex-column p-3">
                                <h5 class="card-title fw-bold mb-1">
                                    <a href="food-details.php?id=<?= $food['id'] ?>" class="text-dark text-decoration-none">
                                        <?= htmlspecialchars($food['name']) ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted small flex-grow-1 mb-3">
                                    <?= htmlspecialchars(mb_strimwidth($food['description'] ?? '', 0, 60, '...')) ?>
                                </p>

                                <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-auto">
                                    <span class="fs-5 fw-bold text-brand"><?= formatPrice($food['price']) ?></span>
                                    
                                    <form action="cart.php" method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="food_id" value="<?= $food['id'] ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-brand btn-sm fw-bold">
                                            <i class="fa-solid fa-cart-plus me-1"></i> Add
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12"><div class="alert alert-warning text-center">No featured dishes available right now.</div></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
:root {
    --brand-primary: #E0533C;
    --brand-hover: #C0392B;
    --brand-gold: #F39C12;
    --brand-light: #FADBD8;
    --brand-subtle: #F5B7B1;
}

/* Banner Unsplash Background Image */
.hero-banner {
    background: url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1600&q=80') center center / cover no-repeat;
}

/* Dark Overlay to make text legible */
.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.65);
    z-index: 1;
}

.text-brand {
    color: var(--brand-primary) !important;
}

.bg-brand-gold {
    background-color: var(--brand-gold) !important;
}

.bg-brand-light {
    background-color: var(--brand-light) !important;
}

.border-brand-subtle {
    border-color: var(--brand-subtle) !important;
}

.btn-brand {
    background-color: var(--brand-primary);
    border-color: var(--brand-primary);
    color: #ffffff;
}
.btn-brand:hover {
    background-color: var(--brand-hover);
    border-color: var(--brand-hover);
    color: #ffffff;
}

.btn-outline-brand {
    color: var(--brand-primary);
    border-color: var(--brand-primary);
}
.btn-outline-brand:hover {
    background-color: var(--brand-primary);
    border-color: var(--brand-primary);
    color: #ffffff;
}

.hover-lift {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.08) !important;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.tracking-wider {
    letter-spacing: 0.5px;
}
</style>

<?php require_once 'includes/footer.php'; ?>