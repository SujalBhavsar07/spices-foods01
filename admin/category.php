<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['admin_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$msg = '';
$edit_cat = null;

// Handle Add / Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $cat_id = isset($_POST['cat_id']) ? (int)$_POST['cat_id'] : 0;
    
    $image_name = $_POST['existing_image'] ?? 'default_category.jpg';

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = __DIR__ . '/../uploads/category/';
        
        // Ensure destination folder exists
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed, true)) {
            $image_name = time() . '_' . uniqid() . '.' . $ext;
            $targetFile = $targetDir . $image_name;
            move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
        }
    }

    if ($cat_id > 0) {
        $stmt = $conn->prepare("UPDATE categories SET name=?, description=?, image=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $description, $image_name, $cat_id);
        $msg = "Category updated successfully!";
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (name, description, image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $description, $image_name);
        $msg = "Category added successfully!";
    }
    $stmt->execute();
    $stmt->close();
}

// Handle Edit Mode Fetch
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $edit_cat = $res->fetch_assoc();
    }
    $stmt->close();
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $stmt->close();
    header("Location: category.php");
    exit();
}

$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="d-flex">
        <!-- Sidebar Navigation -->
        <div class="bg-dark text-white p-3 vh-100 position-fixed" style="width: 250px;">
            <h4 class="fw-bold text-danger mb-4 px-2">Admin Panel</h4>
            <ul class="nav nav-pills flex-column mb-auto gap-1">
                <li><a href="dashboard.php" class="nav-link text-white"><i class="fa-solid fa-chart-line me-2"></i>Dashboard</a></li>
                <li><a href="category.php" class="nav-link active bg-danger text-white fw-bold"><i class="fa-solid fa-list me-2"></i>Categories</a></li>
                <li><a href="subcategory.php" class="nav-link text-white"><i class="fa-solid fa-tags me-2"></i>Subcategories</a></li>
                <li><a href="food.php" class="nav-link text-white"><i class="fa-solid fa-utensils me-2"></i>Foods</a></li>
                <li><a href="users.php" class="nav-link text-white"><i class="fa-solid fa-users me-2"></i>Users</a></li>
                <li><a href="orders.php" class="nav-link text-white"><i class="fa-solid fa-receipt me-2"></i>Orders</a></li>
                <li class="mt-4"><a href="logout.php" class="nav-link text-danger fw-semibold"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
            </ul>
        </div>

        <!-- Main Content Area -->
        <div class="p-4 w-100" style="margin-left: 250px;">
            <h3 class="fw-bold mb-4 text-danger">Manage Categories</h3>

            <?php if ($msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Add / Edit Form Column -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3 rounded-4">
                        <h5 class="fw-bold mb-3 text-danger"><?= $edit_cat ? 'Edit Category' : 'Add New Category' ?></h5>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="cat_id" value="<?= $edit_cat['id'] ?? 0 ?>">
                            <input type="hidden" name="existing_image" value="<?= $edit_cat['image'] ?? 'default_category.jpg' ?>">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Category Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($edit_cat['name'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($edit_cat['description'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Category Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <button type="submit" name="save_category" class="btn btn-danger w-100 fw-bold">Save Category</button>
                        </form>
                    </div>
                </div>

                <!-- Category Table Column -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4 p-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($categories && $categories->num_rows > 0): ?>
                                        <?php while ($row = $categories->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <img src="../uploads/category/<?= htmlspecialchars($row['image']) ?>" class="rounded-circle" style="width:45px; height:45px; object-fit:cover;" alt="Category">
                                                </td>
                                                <td class="fw-bold"><?= htmlspecialchars($row['name']) ?></td>
                                                <td class="small text-muted"><?= htmlspecialchars($row['description'] ?? '') ?></td>
                                                <td class="text-end">
                                                    <a href="category.php?edit=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger me-1"><i class="fa-solid fa-pen"></i></a>
                                                    <a href="category.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this category?');" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No categories found.</td>
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

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>