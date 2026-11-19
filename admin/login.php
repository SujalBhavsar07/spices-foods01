<?php
// Load functions and config

session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to dashboard
if (isAdminLoggedIn()) {
    redirect('admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = sanitize($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($usernameOrEmail) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Query to match either username OR email
        $sql = "SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            // Displays exact DB error if table is missing or query fails
            die("<div style='color:red; font-family:sans-serif; padding:20px;'>
                    <h3>Database Query Failed!</h3>
                    <p>Error: " . htmlspecialchars($conn->error) . "</p>
                    <p><b>Solution:</b> Please run the SQL query from Step 1 in phpMyAdmin to create the <code>admins</code> table.</p>
                 </div>");
        }

        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            // Support both hashed password and plain text check
            if (password_verify($password, $admin['password']) || $password === $admin['password']) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['username'];
                
                setFlash('success', 'Welcome back to the Admin Dashboard!');
                redirect('admin/dashboard.php');
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Admin account not found.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Spices Foods</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #212529; height: 100vh; display: flex; align-items: center; }
        .login-card { max-width: 420px; width: 100%; margin: auto; border-radius: 12px; }
    </style>
</head>
<body>

<div class="container">
    <div class="card login-card shadow-lg bg-dark text-white border-secondary">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <!-- Changed text-warning to text-danger -->
                <i class="fa-solid fa-user-shield text-danger fa-3x mb-2"></i>
                <h3 class="fw-bold text-danger">Admin Panel</h3>
                <p class="text-secondary small">Spices Foods Management</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label text-light small fw-bold">Username or Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-secondary text-white border-secondary"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="username" class="form-control bg-dark text-white border-secondary" placeholder="admin or admin@spicesfoods.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-light small fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-secondary text-white border-secondary"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" class="form-control bg-dark text-white border-secondary" placeholder="••••••••" required>
                    </div>
                </div>

                <!-- Changed btn-warning to btn-danger -->
                <button type="submit" class="btn btn-danger w-100 fw-bold py-2">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> Login to Dashboard
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="../index.php" class="text-secondary text-decoration-none small">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Main Website
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>