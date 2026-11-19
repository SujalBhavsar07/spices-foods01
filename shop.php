<?php
$pageTitle = "Shop Menu - Spices Foods";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/db.php';

$categoryId    = isset($_GET['category']) ? (int)$_GET['category'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$subcategoryId = isset($_GET['subcategory']) ? (int)$_GET['subcategory'] : 0;
$search        = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';

$selectedCategory = null;
if ($categoryId > 0) {
    $cStmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $cStmt->bind_param("i", $categoryId);
    $cStmt->execute();
    $selectedCategory = $cStmt->get_result()->fetch_assoc();
    $cStmt->close();
}

$subcategories = [];
if ($categoryId > 0) {
    $sStmt = $conn->prepare("SELECT * FROM subcategories WHERE category_id = ? ORDER BY id ASC");
    $sStmt->bind_param("i", $categoryId);
    $sStmt->execute();
    $subcategories = $sStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $sStmt->close();
}

$query = "SELECT f.*, c.name AS category_name, s.name AS subcategory_name 
          FROM foods f 
          JOIN subcategories s ON f.subcategory_id = s.id 
          JOIN categories c ON s.category_id = c.id 
          WHERE f.is_available = 1";
$params = [];
$types = "";

if ($categoryId > 0) {
    $query .= " AND s.category_id = ?";
    $params[] = $categoryId;
    $types .= "i";
}
if ($subcategoryId > 0) {
    $query .= " AND f.subcategory_id = ?";
    $params[] = $subcategoryId;
    $types .= "i";
}
if (!empty($search)) {
    $query .= " AND f.name LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

$query .= " ORDER BY f.subcategory_id ASC, f.id DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$foods = $stmt->get_result();
?>

<!-- Clean Shop Header -->
<div class="bg-light py-4 border-bottom mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="h3 fw-bold text-danger mb-1">
                    <?= $selectedCategory ? htmlspecialchars($selectedCategory['name']) : 'Our Menu' ?>
                </h1>
                <p class="text-muted small mb-0">
                    <?= $selectedCategory ? htmlspecialchars($selectedCategory['description'] ?? '') : 'Explore authentic vegetarian dishes.' ?>
                </p>
            </div>
            <div class="col-md-5 mt-3 mt-md-0">
                <form action="shop.php" method="GET" class="d-flex">
                    <?php if ($categoryId > 0): ?>
                        <input type="hidden" name="category" value="<?= $categoryId ?>">
                    <?php endif; ?>
                    <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search food..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-danger btn-sm px-3">Search</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container my-4">

    <!-- Subcategories Tabs -->
    <?php if ($selectedCategory && !empty($subcategories)): ?>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
            <a href="shop.php?category=<?= $categoryId ?>" class="btn <?= ($subcategoryId === 0) ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm px-3">
                All <?= htmlspecialchars($selectedCategory['name']) ?>
            </a>
            <?php foreach ($subcategories as $sub): ?>
                <a href="shop.php?category=<?= $categoryId ?>&subcategory=<?= $sub['id'] ?>" class="btn <?= ($subcategoryId === $sub['id']) ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm px-3">
                    <?= htmlspecialchars($sub['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Foods Grid -->
    <div class="row g-4">
        <?php if ($foods->num_rows > 0): ?>
            <?php while ($food = $foods->fetch_assoc()): ?>
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
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-secondary text-center py-4">
                    <p class="mb-0">No food items found matching your selection.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>