<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Authentication Check: Admin only
if (!isset($_SESSION['admin_id'])) {
    if (isset($_GET['ajax_action'])) {
        header('Content-Type: application/json');
        echo json_encode([]);
        exit();
    }
    header("Location: login.php");
    exit();
}

// Handle AJAX Request to fetch subcategories or foods based on selections
if (isset($_GET['ajax_action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['ajax_action'] === 'get_subcategories' && isset($_GET['cat_id'])) {
        $cat_id = (int)$_GET['cat_id'];
        $stmt = $conn->prepare("SELECT id, name FROM subcategories WHERE category_id = ? ORDER BY name ASC");
        $stmt->bind_param("i", $cat_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        exit();
    }

    if ($_GET['ajax_action'] === 'get_foods' && isset($_GET['subcat_id'])) {
        $subcat_id = (int)$_GET['subcat_id'];
        $stmt = $conn->prepare("SELECT id, name, price, description, is_available FROM foods WHERE subcategory_id = ? ORDER BY name ASC");
        $stmt->bind_param("i", $subcat_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data);
        exit();
    }
}

// Fetch URL Alerts (No Session Used)
$msg = isset($_GET['msg']) ? trim($_GET['msg']) : '';
$msg_type = isset($_GET['type']) ? trim($_GET['type']) : 'success';
$edit_subcat = null;

// ==============================
// Add / Edit Subcategory Action
// ==============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_subcategory'])) {
    $subcat_id   = isset($_POST['subcat_id']) ? (int)$_POST['subcat_id'] : 0;
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $name        = function_exists('sanitize') ? sanitize($conn, $_POST['name']) : trim($_POST['name']);

    if (!empty($name) && $category_id > 0) {
        if ($subcat_id > 0) {
            $stmt = $conn->prepare("UPDATE subcategories SET category_id = ?, name = ? WHERE id = ?");
            $stmt->bind_param("isi", $category_id, $name, $subcat_id);
            $msg_str = $stmt->execute() ? "Subcategory updated successfully!" : "Error updating subcategory.";
        } else {
            $stmt = $conn->prepare("INSERT INTO subcategories (category_id, name) VALUES (?, ?)");
            $stmt->bind_param("is", $category_id, $name);
            $msg_str = $stmt->execute() ? "Subcategory added successfully!" : "Error adding subcategory.";
        }
        $stmt->close();
        $status_type = "success";
    } else {
        $msg_str = "Please select a category and enter a subcategory name.";
        $status_type = "danger";
    }

    header("Location: subcategory.php?msg=" . urlencode($msg_str) . "&type={$status_type}");
    exit();
}

// Fetch Subcategory for Edit
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM subcategories WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $edit_subcat = $result->fetch_assoc();
    }
    $stmt->close();
}

// Delete Subcategory
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM subcategories WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    try {
        if ($stmt->execute()) {
            $msg_str = "Subcategory deleted successfully!";
            $type = "success";
        } else {
            $msg_str = "Failed to delete subcategory.";
            $type = "danger";
        }
    } catch (Exception $e) {
        $msg_str = "Cannot delete: This subcategory is currently linked to food items.";
        $type = "danger";
    }
    $stmt->close();
    header("Location: subcategory.php?msg=" . urlencode($msg_str) . "&type={$type}");
    exit();
}

// Query Categories & Subcategories with food count for presentation
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$subcategories_list = $conn->query("
    SELECT s.*, c.name AS category_name, COUNT(f.id) AS food_count 
    FROM subcategories s 
    JOIN categories c ON s.category_id = c.id 
    LEFT JOIN foods f ON s.id = f.subcategory_id 
    GROUP BY s.id, c.name 
    ORDER BY c.name ASC, s.name ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subcategories - Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; width: 260px; background-color: #212529; }
        .main-content { margin-left: 260px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .table img { border-radius: 8px; }
        .nav-link { color: #cfd8dc; font-weight: 500; }
        .nav-link:hover, .nav-link.active { color: #ffffff; background-color: #dc3545 !important; border-radius: 8px; }
        @media(max-width:768px) {
            .sidebar { width: 100%; position: relative !important; min-height: auto; }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

<div class="d-flex flex-column flex-md-row">

    <!-- Sidebar Navigation -->
    <div class="sidebar text-white p-3 position-fixed">
        <h4 class="fw-bold text-danger mb-4 d-flex align-items-center gap-2">
            <i class="fa-solid fa-utensils"></i> Admin Panel
        </h4>
        <ul class="nav nav-pills flex-column gap-2">
            <li><a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line me-2"></i>Dashboard</a></li>
            <li><a href="category.php" class="nav-link"><i class="fa-solid fa-list me-2"></i>Categories</a></li>
            <li><a href="subcategory.php" class="nav-link active"><i class="fa-solid fa-tags me-2"></i>Subcategories</a></li>
            <li><a href="food.php" class="nav-link"><i class="fa-solid fa-utensils me-2"></i>Foods</a></li>
            <li><a href="users.php" class="nav-link"><i class="fa-solid fa-users me-2"></i>Users</a></li>
            <li><a href="orders.php" class="nav-link"><i class="fa-solid fa-receipt me-2"></i>Orders</a></li>
            <li class="mt-4"><a href="logout.php" class="nav-link text-danger fw-semibold"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="main-content p-4 w-100">

        <h3 class="fw-bold text-danger mb-1">Interactive Subcategory & Menu Explorer</h3>
        <p class="text-muted mb-4">Select a category to filter subcategories and view linked food items dynamically.</p>

        <!-- Alert Notifications -->
        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?= htmlspecialchars($msg_type) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Dynamic Category Explorer Filter -->
        <div class="card p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-filter text-danger me-2"></i> Live Menu Filter</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">1. Select Category</label>
                    <select id="filter_category" class="form-select border-danger">
                        <option value="">-- Choose Category (e.g., Chinese Food) --</option>
                        <?php if ($categories && $categories->num_rows > 0): ?>
                            <?php mysqli_data_seek($categories, 0); while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?= $cat['id']; ?>"><?= htmlspecialchars($cat['name']); ?></option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">2. Select Subcategory</label>
                    <select id="filter_subcategory" class="form-select border-danger" disabled>
                        <option value="">-- Select Category First --</option>
                    </select>
                </div>
            </div>

            <!-- Foods Output Container -->
            <div id="foods_preview_container" class="mt-4 d-none">
                <h6 class="fw-bold text-danger mb-3" id="foods_header">Available Items</h6>
                <div id="foods_list_box"></div>
            </div>
        </div>

        <div class="row g-4">

            <!-- Subcategory Add / Edit Form -->
            <div class="col-lg-4">
                <div class="card p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="fa-solid <?= $edit_subcat ? 'fa-pen-to-square' : 'fa-plus' ?> text-danger me-2"></i>
                        <?= $edit_subcat ? 'Edit Subcategory' : 'Add Subcategory' ?>
                    </h5>

                    <form method="POST" action="subcategory.php">
                        <input type="hidden" name="subcat_id" value="<?= $edit_subcat['id'] ?? 0 ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Parent Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php 
                                mysqli_data_seek($categories, 0); 
                                while ($cat = $categories->fetch_assoc()): 
                                ?>
                                    <option value="<?= $cat['id']; ?>" <?= (isset($edit_subcat['category_id']) && $edit_subcat['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Subcategory Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Veg Soups" value="<?= htmlspecialchars($edit_subcat['name'] ?? '') ?>" required>
                        </div>

                        <button type="submit" name="save_subcategory" class="btn btn-danger w-100">
                            <i class="fa-solid fa-floppy-disk me-2"></i>
                            <?= $edit_subcat ? 'Update Subcategory' : 'Save Subcategory' ?>
                        </button>

                        <?php if ($edit_subcat): ?>
                            <a href="subcategory.php" class="btn btn-outline-secondary w-100 mt-2">Cancel Edit</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Subcategories Table Overview -->
            <div class="col-lg-8">
                <div class="card p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Subcategory</th>
                                    <th>Category</th>
                                    <th>Linked Foods</th>
                                    <th class="text-end" width="120">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($subcategories_list && $subcategories_list->num_rows > 0): ?>
                                    <?php while ($row = $subcategories_list->fetch_assoc()): ?>
                                        <tr>
                                            <td class="fw-bold text-danger">#<?= $row['id']; ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($row['name']); ?></td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($row['category_name']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">
                                                    <?= (int)$row['food_count']; ?> Item(s)
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="subcategory.php?edit=<?= $row['id']; ?>" class="btn btn-outline-danger btn-sm me-1" title="Edit">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <a href="subcategory.php?delete=<?= $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this subcategory?')" class="btn btn-outline-danger btn-sm" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-box-open fa-3x mb-3"></i>
                                            <p class="mb-0">No subcategories found.</p>
                                        </td>
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

<!-- JavaScript for Dynamic Ajax Filtering -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterCategory = document.getElementById('filter_category');
    const filterSubcategory = document.getElementById('filter_subcategory');
    const foodsContainer = document.getElementById('foods_preview_container');
    const foodsListBox = document.getElementById('foods_list_box');
    const foodsHeader = document.getElementById('foods_header');

    // 1. When Category dropdown changes
    filterCategory.addEventListener('change', function () {
        const catId = this.value;

        filterSubcategory.innerHTML = '<option value="">-- Loading Subcategories... --</option>';
        filterSubcategory.disabled = true;
        foodsContainer.classList.add('d-none');

        if (!catId) {
            filterSubcategory.innerHTML = '<option value="">-- Select Category First --</option>';
            return;
        }

        fetch(`subcategory.php?ajax_action=get_subcategories&cat_id=${catId}`)
            .then(res => res.json())
            .then(data => {
                filterSubcategory.innerHTML = '<option value="">-- Select Subcategory (e.g., Veg Soups) --</option>';
                if (data.length > 0) {
                    data.forEach(subcat => {
                        filterSubcategory.innerHTML += `<option value="${subcat.id}">${subcat.name}</option>`;
                    });
                    filterSubcategory.disabled = false;
                } else {
                    filterSubcategory.innerHTML = '<option value="">No subcategories available</option>';
                }
            });
    });

    // 2. When Subcategory dropdown changes
    filterSubcategory.addEventListener('change', function () {
        const subcatId = this.value;
        const subcatText = filterSubcategory.options[filterSubcategory.selectedIndex].text;

        if (!subcatId) {
            foodsContainer.classList.add('d-none');
            return;
        }

        fetch(`subcategory.php?ajax_action=get_foods&subcat_id=${subcatId}`)
            .then(res => res.json())
            .then(foods => {
                foodsHeader.innerText = `Items in "${subcatText}" (${foods.length})`;
                foodsListBox.innerHTML = '';

                if (foods.length > 0) {
                    foods.forEach(food => {
                        foodsListBox.innerHTML += `
                            <div class="food-item-card d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 fw-bold text-dark">${food.name}</h6>
                                    <small class="text-muted">${food.description || 'No description available'}</small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-danger">₹${parseFloat(food.price).toFixed(2)}</span>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    foodsListBox.innerHTML = '<p class="text-muted my-2">No food items added under this subcategory yet.</p>';
                }
                foodsContainer.classList.remove('d-none');
            });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>