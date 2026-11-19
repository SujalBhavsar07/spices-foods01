<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['category']) ? (int)$_GET['category'] : 1);
$subcategoryId = isset($_GET['subcategory']) ? (int)$_GET['subcategory'] : 0;

$catStmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$catStmt->bind_param("i", $categoryId);
$catStmt->execute();
$category = $catStmt->get_result()->fetch_assoc();
$catStmt->close();

if (!$category) {
    $firstCat = $conn->query("SELECT * FROM categories ORDER BY id ASC LIMIT 1")->fetch_assoc();
    if ($firstCat) {
        $category = $firstCat;
        $categoryId = $category['id'];
    } else {
        header("Location: shop.php");
        exit();
    }
}

$pageTitle = htmlspecialchars($category['name']) . " - Spices Foods";
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$subStmt = $conn->prepare("SELECT * FROM subcategories WHERE category_id = ? ORDER BY id ASC");
$subStmt->bind_param("i", $categoryId);
$subStmt->execute();
$subcategories = $subStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$subStmt->close();

$foodQuery = "SELECT f.*, c.name AS category_name, s.name AS subcategory_name 
              FROM foods f 
              JOIN subcategories s ON f.subcategory_id = s.id 
              JOIN categories c ON s.category_id = c.id 
              WHERE s.category_id = ? AND f.is_available = 1";
$params = [$categoryId];
$types = "i";

if ($subcategoryId > 0) {
    $foodQuery .= " AND f.subcategory_id = ?";
    $params[] = $subcategoryId;
    $types .= "i";
}

$foodQuery .= " ORDER BY f.subcategory_id ASC, f.id DESC";

$foodStmt = $conn->prepare($foodQuery);
$foodStmt->bind_param($types, ...$params);
$foodStmt->execute();
$foods = $foodStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$foodStmt->close();

$activeSubcatName = "All " . $category['name'];
if ($subcategoryId > 0) {
    foreach ($subcategories as $sub) {
        if ($sub['id'] == $subcategoryId) {
            $activeSubcatName = $sub['name'];
            break;
        }
    }
}
?>

<!-- Clean Category Header -->
<div class="bg-light py-4 border-bottom">
    <div class="container">
        <h1 class="fw-bold text-danger mb-1"><?= htmlspecialchars($category['name']) ?></h1>
        <p class="text-muted mb-0"><?= htmlspecialchars($category['description'] ?? 'Browse delicious menu items.') ?></p>
    </div>
</div>

<div class="container my-4">

    <!-- Subcategory Navigation Tabs -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <a href="category.php?id=<?= $categoryId ?>" class="btn <?= ($subcategoryId === 0) ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm px-3">
            All Items
        </a>
        <?php foreach ($subcategories as $sub): ?>
            <a href="category.php?id=<?= $categoryId ?>&subcategory=<?= $sub['id'] ?>" class="btn <?= ($subcategoryId === $sub['id']) ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm px-3">
                <?= htmlspecialchars($sub['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Active Subcategory Heading -->
    <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-4">
        <h4 class="fw-bold text-dark mb-0"><?= htmlspecialchars($activeSubcatName) ?></h4>
        <span class="text-muted small"><?= count($foods) ?> Food Item(s)</span>
    </div>

    <!-- Foods Grid -->
    <?php if (!empty($foods)): ?>
        <div class="row g-4">
            <?php foreach ($foods as $food): ?>
                <?php 
                    $foodImg = !empty($food['image']) && file_exists('uploads/foods/' . $food['image']) 
                        ? 'uploads/foods/' . htmlspecialchars($food['image']) 
                        : 'uploads/foods/default_food.jpg';
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="<?= $foodImg ?>" 
                             class="card-img-top" 
                             style="height: 180px; object-fit: cover;" 
                             alt="<?= htmlspecialchars($food['name']) ?>"
                             onerror="this.src='uploads/foods/default_food.jpg';">
                        
                        <div class="card-body d-flex flex-column p-3">
                            <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($food['name']) ?></h6>
                            
                            <p class="text-muted small flex-grow-1 mb-2" style="font-size: 0.85rem; line-height: 1.3;">
                                <?= htmlspecialchars($food['description'] ?? '') ?>
                            </p>

                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="fs-5 fw-bold text-danger">₹<?= number_format($food['price'], 2) ?></span>
                                <span class="badge bg-success small">Veg</span>
                            </div>

                            <div class="d-grid gap-2">
                                <form action="cart.php" method="POST" class="w-100 mb-1">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="food_id" value="<?= $food['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold">
                                        <i class="fa-solid fa-cart-shopping me-1"></i> Add to Cart
                                    </button>
                                </form>
                                <a href="food-details.php?id=<?= $food['id'] ?>" class="btn btn-outline-secondary btn-sm w-100">
                                    View Details
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary text-center py-4 my-3">
            <p class="mb-0">No food items found in this section.</p>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
