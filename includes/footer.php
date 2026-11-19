</main> <!-- End Main Container -->

<!-- Aesthetic Red Footer Styling -->
<style>
    .aesthetic-footer {
        background-color: var(--aesthetic-red-dark, #120708) !important;
        border-top: 1px solid rgba(196, 30, 58, 0.25);
    }

    .aesthetic-footer .brand-text {
        color: var(--aesthetic-red-glow, #E32636) !important;
    }

    .aesthetic-footer .icon-accent {
        color: var(--aesthetic-red-accent, #C41E3A) !important;
    }

    .aesthetic-footer .badge-veg {
        background-color: rgba(46, 125, 50, 0.2);
        color: #4ADE80;
        border: 1px solid rgba(74, 222, 128, 0.3);
    }

    .aesthetic-footer .footer-link {
        color: #9CA3AF !important;
        transition: color 0.25s ease, transform 0.25s ease;
    }

    .aesthetic-footer .footer-link:hover {
        color: var(--aesthetic-red-glow, #E32636) !important;
    }

    .aesthetic-footer .btn-admin {
        border-color: rgba(196, 30, 58, 0.4) !important;
        color: #E0E0E0 !important;
        background-color: var(--aesthetic-red-surface, #1E0C0E);
        transition: all 0.3s ease;
    }

    .aesthetic-footer .btn-admin:hover {
        background-color: var(--aesthetic-red-accent, #C41E3A) !important;
        border-color: var(--aesthetic-red-accent, #C41E3A) !important;
        color: #ffffff !important;
        box-shadow: 0 0 10px rgba(227, 38, 54, 0.3);
    }

    .aesthetic-footer .border-accent {
        border-color: rgba(196, 30, 58, 0.2) !important;
    }
</style>

<footer class="aesthetic-footer text-white pt-5 pb-3 mt-auto">
    <div class="container">
        <div class="row g-4">
            <!-- Brand Info -->
            <div class="col-md-4">
                <h5 class="fw-bold brand-text mb-3">
                    <i class="fa-solid fa-utensils me-2 icon-accent"></i>Spices Foods
                </h5>
                <p class="text-secondary small leading-relaxed">
                    Serving authentic 100% Pure Vegetarian Indian curries, aromatic biryanis, and delicious Indo-Chinese delicacies made with natural spices and fresh ingredients.
                </p>
                <div class="d-flex gap-2">
                    <span class="badge badge-veg px-2 py-1"><i class="fa-solid fa-leaf me-1"></i> 100% Pure Veg</span>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-md-4">
                <h6 class="fw-bold text-light mb-3">Quick Links</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li><a href="index.php" class="footer-link text-decoration-none">Home</a></li>
                    <li><a href="shop.php" class="footer-link text-decoration-none">Full Menu</a></li>
                    <li><a href="cart.php" class="footer-link text-decoration-none">My Cart</a></li>
                    <li><a href="my-orders.php" class="footer-link text-decoration-none">Track Orders</a></li>
                </ul>
            </div>

            <!-- Contact & Hours -->
            <div class="col-md-4">
                <h6 class="fw-bold text-light mb-3">Contact Us</h6>
                <p class="text-secondary small mb-1"><i class="fa-solid fa-location-dot me-2 icon-accent"></i> 123 Food Street, Tasty City</p>
                <p class="text-secondary small mb-1"><i class="fa-solid fa-phone me-2 icon-accent"></i> +91 98765 43210</p>
                <p class="text-secondary small mb-3"><i class="fa-solid fa-envelope me-2 icon-accent"></i> support@spicesfoods.com</p>
                <small class="text-white d-block mb-3">Open Hours: 11:00 AM - 11:00 PM</small>
                
                <!-- Admin Login Button/Link -->
                <a href="admin/login.php" class="btn btn-admin btn-sm rounded-pill px-3" style="font-size: 0.75rem;">
                    <i class="fa-solid fa-user-shield me-1 icon-accent"></i> Admin Panel
                </a>
            </div>
        </div>

        <hr class="border-accent my-4">

        <!-- Copyright & Admin Link -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-secondary small">
            <p class="mb-0">&copy; <?= date('Y') ?> Spices Foods. All Rights Reserved.</p>
            <div class="d-flex align-items-center gap-3">
                <span>Pure Vegetarian Food Delivery</span>
                <span class="text-secondary">|</span>
                <a href="admin/login.php" class="footer-link text-decoration-none small">
                    <i class="fa-solid fa-lock me-1 icon-accent"></i> Admin Login
                </a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>