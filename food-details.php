<?php
$pageTitle = "Food Details - Spices Foods";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch main food item
$stmt = $conn->prepare("SELECT f.*, s.category_id, c.name as category_name, s.name as subcategory_name 
                        FROM foods f 
                        JOIN subcategories s ON f.subcategory_id = s.id 
                        JOIN categories c ON s.category_id = c.id 
                        WHERE f.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$food = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$food) {
    redirect('shop.php');
}

// Dynamic query: Fetch related items from the same category (excluding current item)
$cat1_stmt = $conn->prepare("SELECT f.* FROM foods f JOIN subcategories s ON f.subcategory_id = s.id WHERE s.category_id = ? AND f.id != ? AND f.is_available = 1 LIMIT 3");
$cat1_stmt->bind_param("ii", $food['category_id'], $id);
$cat1_stmt->execute();
$related_foods = $cat1_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$cat1_stmt->close();

// Fetch popular items from other categories
$cat2_stmt = $conn->prepare("SELECT f.* FROM foods f JOIN subcategories s ON f.subcategory_id = s.id WHERE s.category_id != ? AND f.is_available = 1 LIMIT 3");
$cat2_stmt->bind_param("i", $food['category_id']);
$cat2_stmt->execute();
$other_foods = $cat2_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$cat2_stmt->close();
?>

<div class="container my-5">
    <!-- Main Food Details Section -->
    <div class="row g-4 align-items-center mb-5 bg-white p-4 rounded-4 shadow-sm border">
        <div class="col-md-6">
            <?php 
                $foodImg = !empty($food['image']) && file_exists('uploads/foods/' . $food['image']) 
                    ? 'uploads/foods/' . htmlspecialchars($food['image']) 
                    : 'uploads/foods/default_food.jpg';
            ?>
            <img src="<?= $foodImg ?>" 
                 alt="<?= htmlspecialchars($food['name']) ?>" 
                 class="img-fluid rounded-4 shadow-sm w-100" 
                 style="max-height: 400px; object-fit: cover;">
        </div>
        <div class="col-md-6">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small fw-bold">
                    <i class="fa-solid fa-leaf me-1"></i> 100% Pure Veg
                </span>
                <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill small">
                    <?= htmlspecialchars($food['category_name']) ?>
                </span>
            </div>

            <h1 class="fw-bold text-dark display-6 mb-3"><?= htmlspecialchars($food['name']) ?></h1>
            <h2 class="text-brand fw-bold mb-3"><?= formatPrice($food['price']) ?></h2>
            <p class="text-muted leading-relaxed mb-4"><?= nl2br(htmlspecialchars($food['description'] ?? '')) ?></p>

            <form action="cart.php" method="POST" class="mt-4">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="food_id" value="<?= $food['id'] ?>">
                
                <div class="d-flex align-items-center gap-3">
                    <div class="input-group" style="width: 130px;">
                        <span class="input-group-text bg-light border-end-0">Qty</span>
                        <input type="number" name="quantity" value="1" min="1" max="<?= (int)$food['stock'] ?>" class="form-control text-center fw-bold border-start-0">
                    </div>
                    <button type="submit" class="btn btn-brand btn-lg fw-bold px-4 rounded-3 shadow-sm">
                        <i class="fa-solid fa-cart-plus me-2"></i>Add to Cart
                    </button>
                </div>
            </form>
        </div>
    </div>

    <hr class="my-5 opacity-10">

    <div class="text-center mb-5">
        <h3 class="fw-bold text-dark mb-1">More Delights from Our Kitchen</h3>
        <p class="text-muted">Explore popular picks and related items</p>
    </div>

    <!-- Related Category Items -->
    <?php if (!empty($related_foods)): ?>
    <div class="category-section mb-5">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
            <h4 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-utensils text-brand me-2"></i> Similar in <?= htmlspecialchars($food['category_name']) ?>
            </h4>
            <a href="shop.php?category=<?= $food['category_id'] ?>" class="text-brand fw-semibold text-decoration-none small">
                View All <?= htmlspecialchars($food['category_name']) ?> <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($related_foods as $item): ?>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden hover-lift">
                        <?php 
                            $itemImg = !empty($item['image']) && file_exists('uploads/foods/' . $item['image']) 
                                ? 'uploads/foods/' . htmlspecialchars($item['image']) 
                                : 'uploads/foods/default_food.jpg';
                        ?>
                        <img src="<?= $itemImg ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column p-3">
                            <h5 class="fw-bold fs-6 mb-1 text-truncate"><?= htmlspecialchars($item['name']) ?></h5>
                            <p class="text-brand fw-bold mb-3"><?= formatPrice($item['price']) ?></p>
                            <a href="food-details.php?id=<?= $item['id'] ?>" class="btn btn-outline-brand btn-sm fw-bold w-100 mt-auto">
                                View Item
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Other Recommendations -->
    <?php if (!empty($other_foods)): ?>
    <div class="category-section">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
            <h4 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-bowl-food text-brand me-2"></i> You Might Also Like
            </h4>
            <a href="shop.php" class="text-brand fw-semibold text-decoration-none small">
                View Full Menu <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($other_foods as $item): ?>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden hover-lift">
                        <?php 
                            $itemImg = !empty($item['image']) && file_exists('uploads/foods/' . $item['image']) 
                                ? 'uploads/foods/' . htmlspecialchars($item['image']) 
                                : 'uploads/foods/default_food.jpg';
                        ?>
                        <img src="<?= $itemImg ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column p-3">
                            <h5 class="fw-bold fs-6 mb-1 text-truncate"><?= htmlspecialchars($item['name']) ?></h5>
                            <p class="text-brand fw-bold mb-3"><?= formatPrice($item['price']) ?></p>
                            <a href="food-details.php?id=<?= $item['id'] ?>" class="btn btn-outline-brand btn-sm fw-bold w-100 mt-auto">
                                View Item
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
:root {
    --brand-primary: #E0533C;
    --brand-hover: #C0392B;
    --brand-light: #FADBD8;
}

.text-brand {
    color: var(--brand-primary) !important;
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
    transform: translateY(-4px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}
</style>

<?php require_once 'includes/footer.php'; ?>