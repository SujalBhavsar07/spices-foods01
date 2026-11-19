<?php
$pageTitle = "Login - Spices Foods";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/db.php';

if (isUserLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!empty($email) && !empty($pass)) {
        $stmt = $conn->prepare("SELECT id, name, password, is_active FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($pass, $user['password'])) {
            if ($user['is_active'] == 1) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                setFlash('success', 'Welcome back, ' . $user['name'] . '!');
                redirect('index.php');
            } else {
                $error = "Your account is deactivated. Contact support.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <!-- Changed text-warning to text-danger -->
                    <h3 class="card-title text-center mb-4 text-danger fw-bold">Login to Account</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <!-- Changed btn-warning to btn-danger -->
                        <button type="submit" class="btn btn-danger w-100 fw-bold">Login</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <!-- Changed text-warning to text-danger -->
                        <small>Don't have an account? <a href="register.php" class="text-danger">Register here</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php require_once 'includes/footer.php'; ?>