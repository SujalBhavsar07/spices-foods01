<?php
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Admin Authentication Check
if (!isset($_SESSION['admin_id'])) { 
    if (isset($_GET['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
    header("Location: login.php"); 
    exit(); 
}

// ==========================================
// 1. AJAX API ENDPOINT FOR STOCK DEDUCTION
// ==========================================
if (isset($_GET['action']) && $_GET['action'] === 'deduct_stock') {
    header('Content-Type: application/json');
    
    $food_id  = isset($_POST['food_id']) ? (int)$_POST['food_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    if ($food_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
        exit();
    }

    // Begin database transaction for safe stock deduction
    $conn->begin_transaction();

    try {
        // Atomic update: ensure stock is sufficient before deducting
        $stmt = $conn->prepare("UPDATE foods SET stock = stock - ? WHERE id = ? AND stock >= ?");
        $stmt->bind_param("iii", $quantity, $food_id, $quantity);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $conn->commit();
            
            // Get updated stock level to return back to UI
            $sStmt = $conn->prepare("SELECT stock FROM foods WHERE id = ?");
            $sStmt->bind_param("i", $food_id);
            $sStmt->execute();
            $new_stock = $sStmt->get_result()->fetch_assoc()['stock'];
            $sStmt->close();

            echo json_encode(['success' => true, 'new_stock' => $new_stock, 'message' => 'Stock updated.']);
        } else {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Insufficient stock available.']);
        }
        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
    }
    exit();
}

$msg = '';
$edit_food = null;

// ==========================================
// 3. HANDLE ADD / EDIT FOOD ITEM
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_food'])) {
    $food_id        = isset($_POST['food_id']) ? (int)$_POST['food_id'] : 0;
    $subcategory_id = (int)($_POST['subcategory_id'] ?? 0);
    $name           = trim($_POST['name'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $price          = (float)($_POST['price'] ?? 0);
    $stock          = (int)($_POST['stock'] ?? 0);
    $featured       = isset($_POST['featured']) ? 1 : 0;
    $existing_image = $_POST['existing_image'] ?? 'default_food.jpg';
    
    $image_name = $existing_image;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = __DIR__ . '/../uploads/foods/';
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed, true)) {
            $image_name = time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . $image_name);
        }
    }

    if ($food_id > 0) {
        $stmt = $conn->prepare("UPDATE foods SET subcategory_id = ?, name = ?, description = ?, price = ?, stock = ?, featured = ?, image = ? WHERE id = ?");
        $stmt->bind_param("issdissi", $subcategory_id, $name, $description, $price, $stock, $featured, $image_name, $food_id);
        $stmt->execute();
        $stmt->close();
        $msg = "Food item updated successfully!";
    } else {
        $stmt = $conn->prepare("INSERT INTO foods (subcategory_id, name, description, price, stock, featured, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdiss", $subcategory_id, $name, $description, $price, $stock, $featured, $image_name);
        $stmt->execute();
        $stmt->close();
        $msg = "Food item added successfully!";
    }
    header("Location: food.php?msg=" . urlencode($msg));
    exit();
}

// ==========================================
// 4. FETCH FOOD ITEM FOR EDIT
// ==========================================
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $eStmt = $conn->prepare("SELECT f.*, s.category_id FROM foods f JOIN subcategories s ON f.subcategory_id = s.id WHERE f.id = ?");
    $eStmt->bind_param("i", $edit_id);
    $eStmt->execute();
    $edit_food = $eStmt->get_result()->fetch_assoc();
    $eStmt->close();
}

// ==========================================
// 5. DELETE FOOD ITEM
// ==========================================
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $delStmt = $conn->prepare("DELETE FROM foods WHERE id = ?");
    $delStmt->bind_param("i", $del_id);
    $delStmt->execute();
    $delStmt->close();
    header("Location: food.php?msg=" . urlencode("Food item deleted successfully."));
    exit();
}

// Fetch categories and food items list
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$foods = $conn->query("SELECT f.*, c.name as category_name, s.name as subcategory_name 
                       FROM foods f 
                       JOIN subcategories s ON f.subcategory_id = s.id 
                       JOIN categories c ON s.category_id = c.id 
                       ORDER BY f.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Foods - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; width: 250px; background-color: #212529; }
        .main-content { margin-left: 250px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar Navigation -->
        <div class="sidebar bg-dark text-white p-3 vh-100 position-fixed">
            <h4 class="fw-bold text-danger mb-4 px-2">Admin Panel</h4>
            <ul class="nav nav-pills flex-column mb-auto gap-1">
                <li><a href="dashboard.php" class="nav-link text-white"><i class="fa-solid fa-chart-line me-2"></i>Dashboard</a></li>
                <li><a href="category.php" class="nav-link text-white"><i class="fa-solid fa-list me-2"></i>Categories</a></li>
                <li><a href="subcategory.php" class="nav-link text-white"><i class="fa-solid fa-tags me-2"></i>Subcategories</a></li>
                <li><a href="food.php" class="nav-link active bg-danger text-white fw-bold"><i class="fa-solid fa-utensils me-2"></i>Foods</a></li>
                <li><a href="users.php" class="nav-link text-white"><i class="fa-solid fa-users me-2"></i>Users</a></li>
                <li><a href="orders.php" class="nav-link text-white"><i class="fa-solid fa-receipt me-2"></i>Orders</a></li>
                <li class="mt-4"><a href="logout.php" class="nav-link text-danger fw-semibold"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
            </ul>
        </div>

        <div class="main-content p-4 w-100">
            <h3 class="fw-bold mb-4 text-danger">Manage Food Menu</h3>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_GET['msg']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Add / Edit Food Form -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 rounded-4">
                        <h5 class="fw-bold mb-3 text-danger">
                            <i class="fa-solid <?= $edit_food ? 'fa-pen-to-square' : 'fa-plus' ?> me-2"></i>
                            <?= $edit_food ? 'Edit Food Item' : 'Add Food Item' ?>
                        </h5>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="food_id" value="<?= $edit_food['id'] ?? 0 ?>">
                            <input type="hidden" name="existing_image" value="<?= $edit_food['image'] ?? 'default_food.jpg' ?>">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">1. Select Category</label>
                                <select name="category_id" id="food_category_id" class="form-select" required>
                                    <option value="">-- Select Category --</option>
                                    <?php 
                                    mysqli_data_seek($categories, 0);
                                    while ($c = $categories->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $c['id'] ?>" <?= (isset($edit_food['category_id']) && $edit_food['category_id'] == $c['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">2. Select Subcategory</label>
                                <select name="subcategory_id" id="food_subcategory_id" class="form-select" required>
                                    <option value="">-- Select Category First --</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Food Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Veg Biryani" value="<?= htmlspecialchars($edit_food['name'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Price (₹)</label>
                                <input type="number" step="0.01" name="price" class="form-control" placeholder="210.00" value="<?= htmlspecialchars($edit_food['price'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Stock Quantity</label>
                                <input type="number" name="stock" class="form-control" value="<?= htmlspecialchars($edit_food['stock'] ?? 100) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Brief food description..."><?= htmlspecialchars($edit_food['description'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Food Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>

                            <div class="form-check mb-4">
                                <input type="checkbox" name="featured" class="form-check-input" id="feat" <?= (!empty($edit_food['featured'])) ? 'checked' : '' ?>>
                                <label class="form-check-label small fw-semibold" for="feat">Mark as Featured Item</label>
                            </div>

                            <button type="submit" name="save_food" class="btn btn-danger w-100 fw-bold">
                                <i class="fa-solid fa-floppy-disk me-2"></i>
                                <?= $edit_food ? 'Update Food Item' : 'Save Food Item' ?>
                            </button>

                            <?php if ($edit_food): ?>
                                <a href="food.php" class="btn btn-outline-secondary w-100 mt-2">Cancel Edit</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Food Table -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Food</th>
                                        <th>Category / Subcat</th>
                                        <th>Price</th>
                                        <th>Stock Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($foods && $foods->num_rows > 0): ?>
                                        <?php while ($row = $foods->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="../uploads/foods/<?= htmlspecialchars($row['image']) ?>" class="rounded me-2" style="width:45px; height:45px; object-fit:cover;" onerror="this.src='../uploads/foods/default_food.jpg';">
                                                        <div>
                                                            <span class="fw-bold d-block"><?= htmlspecialchars($row['name']) ?></span>
                                                            <?php if ($row['featured']): ?>
                                                                <span class="badge bg-warning text-dark extra-small">Featured</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-dark"><?= htmlspecialchars($row['category_name']) ?></span>
                                                    <span class="badge bg-secondary"><?= htmlspecialchars($row['subcategory_name']) ?></span>
                                                </td>
                                                <td class="fw-bold text-danger">₹<?= number_format($row['price'], 2) ?></td>
                                                
                                                <!-- Dynamic Stock Column -->
                                                <td>
                                                    <?php if ($row['stock'] <= 0): ?>
                                                        <span class="badge bg-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Out of Stock (0)</span>
                                                    <?php elseif ($row['stock'] <= 5): ?>
                                                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-triangle-exclamation me-1"></i> Low Stock (<?= $row['stock'] ?>)</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark border"><?= $row['stock'] ?> left</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td class="text-end">
                                                    <a href="food.php?edit=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger me-1"><i class="fa-solid fa-pen"></i></a>
                                                    <a href="food.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete food item?');" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No food items found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const catSelect = document.getElementById('food_category_id');
        const subcatSelect = document.getElementById('food_subcategory_id');
        const selectedSubcatId = <?= json_encode($edit_food['subcategory_id'] ?? 0) ?>;

        function loadSubcategories(catId, selectTarget) {
            subcatSelect.innerHTML = '<option value="">Loading...</option>';
            if (!catId) {
                subcatSelect.innerHTML = '<option value="">-- Select Category First --</option>';
                return;
            }
            fetch(`subcategory.php?ajax_action=get_subcategories&cat_id=${catId}`)
                .then(res => res.json())
                .then(data => {
                    subcatSelect.innerHTML = '<option value="">-- Select Subcategory --</option>';
                    data.forEach(sub => {
                        const isSel = (selectTarget && parseInt(selectTarget) === parseInt(sub.id)) ? 'selected' : '';
                        subcatSelect.innerHTML += `<option value="${sub.id}" ${isSel}>${sub.name}</option>`;
                    });
                });
        }

        if (catSelect && subcatSelect) {
            if (catSelect.value) {
                loadSubcategories(catSelect.value, selectedSubcatId);
            }

            catSelect.addEventListener('change', function() {
                loadSubcategories(this.value, 0);
            });
        }
    });
    </script>
</body>
</html>