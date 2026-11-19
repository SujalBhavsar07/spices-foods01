<?php
$pageTitle = "Register - Spices Foods";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/db.php';

if (isUserLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$name = $email = $phone = $address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($conn, $_POST['name'] ?? '');
    $email   = sanitize($conn, $_POST['email'] ?? '');
    $phone   = sanitize($conn, $_POST['phone'] ?? '');
    $address = sanitize($conn, $_POST['address'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($pass)) {
        $errors[] = "All required fields must be filled out.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if ($pass !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email is already registered.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashedPass = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $hashedPass, $address);
        
        if ($stmt->execute()) {
            setFlash('success', 'Registration successful! You can now log in.');
            redirect('login.php');
        } else {
            $errors[] = "An error occurred. Please try again.";
        }
        $stmt->close();
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <!-- Changed text-warning to text-danger -->
                    <h3 class="card-title text-center mb-4 text-danger fw-bold">Create Account</h3>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="register.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number *</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($address) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <!-- Changed btn-warning to btn-danger -->
                        <button type="submit" class="btn btn-danger w-100 fw-bold">Register</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <!-- Changed text-warning to text-danger -->
                        <small>Already have an account? <a href="login.php" class="text-danger">Login here</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php require_once 'includes/footer.php'; ?>