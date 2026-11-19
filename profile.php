<?php
$pageTitle = "My Profile - Spices Foods";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/db.php';

requireUserLogin();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($conn, $_POST['name'] ?? '');
    $phone   = sanitize($conn, $_POST['phone'] ?? '');
    $address = sanitize($conn, $_POST['address'] ?? '');

    $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $phone, $address, $userId);
    if ($stmt->execute()) {
        $_SESSION['user_name'] = $name;
        setFlash('success', 'Profile updated successfully.');
    }
    $stmt->close();
    redirect('profile.php');
}

$stmt = $conn->prepare("SELECT name, email, phone, address FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4 shadow-sm border-0">
                <!-- Heading styled in red -->
                <h3 class="fw-bold mb-3 text-danger">Your Account Profile</h3>
                
                <form action="profile.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email (Read Only)</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Default Delivery Address</label>
                        <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
                    </div>
                    <!-- Changed button from warning (yellow) to danger (red) -->
                    <button type="submit" class="btn btn-danger fw-bold">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</div>


<?php require_once 'includes/footer.php'; ?>