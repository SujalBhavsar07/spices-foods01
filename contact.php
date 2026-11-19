<?php
$pageTitle = "Contact Us - Spices Foods";
require_once 'includes/header.php';
require_once 'includes/navbar.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = "Thank you! Your message has been received. We will get back to you shortly.";
}
?>

<!-- Aesthetic Light Contact Form Styling -->
<style>
    .contact-card {
        background-color: #FFFFFF !important;
        border: 1px solid var(--aesthetic-red-border, #F3D2D2) !important;
        box-shadow: 0 8px 24px rgba(139, 0, 0, 0.04) !important;
        border-radius: 16px !important;
    }

    .contact-title {
        color: var(--aesthetic-red-primary, #8B0000) !important;
    }

    .contact-form .form-control {
        border: 1px solid var(--aesthetic-red-border, #F3D2D2);
        border-radius: 8px;
        padding: 0.65rem 0.9rem;
    }

    .contact-form .form-control:focus {
        border-color: var(--aesthetic-red-accent, #C41E3A);
        box-shadow: 0 0 0 0.2rem rgba(196, 30, 58, 0.15);
    }
</style>

<div class="container my-5">
    <div class="row g-4 justify-content-center">
        <div class="col-md-8">
            <div class="card p-4 p-md-5 contact-card">
                <h2 class="fw-bold text-center mb-2 contact-title">Get in Touch</h2>
                <p class="text-center text-muted mb-4">Have questions or feedback? We'd love to hear from you.</p>

                <?php if ($msg): ?>
                    <div class="alert alert-success d-flex align-items-center mb-4">
                        <i class="fa-solid fa-circle-check me-2 fs-5"></i>
                        <div><?= htmlspecialchars($msg) ?></div>
                    </div>
                <?php endif; ?>

                <form action="contact.php" method="POST" class="contact-form">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Your Name</label>
                        <input type="text" class="form-control" required placeholder="Enter your full name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Your Email</label>
                        <input type="email" class="form-control" required placeholder="name@example.com">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-medium">Message</label>
                        <textarea class="form-control" rows="4" required placeholder="How can we help you?"></textarea>
                    </div>
                    <button type="submit" class="btn btn-aesthetic-red w-100 fw-bold py-2 fs-5">
                        <i class="fa-paper-plane fa-solid me-2"></i>Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>