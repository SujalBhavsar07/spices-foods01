<footer class="bg-dark text-white pt-5 pb-3 mt-auto">
    <div class="container">
        <div class="row g-4">
            <!-- Brand Info -->
            <div class="col-md-4">
                <!-- Changed text-warning to text-danger -->
                <h5 class="fw-bold text-danger mb-3">
                    <i class="fa-solid fa-utensils me-2"></i>Spices Foods
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
                <ul class="list-unstyled text-secondary small d-flex flex-column gap-2">
                    <!-- Changed hover-warning to hover-danger -->
                    <li><a href="index.php" class="text-secondary text-decoration-none hover-danger">Home</a></li>
                    <li><a href="shop.php" class="text-secondary text-decoration-none hover-danger">Full Menu</a></li>
                    <li><a href="cart.php" class="text-secondary text-decoration-none hover-danger">My Cart</a></li>
                    <li><a href="my-orders.php" class="text-secondary text-decoration-none hover-danger">Track Orders</a></li>
                </ul>
            </div>

            <!-- Contact & Hours -->
            <div class="col-md-4">
                <h6 class="fw-bold text-light mb-3">Contact Us</h6>
                <!-- Changed icons from text-warning to text-danger -->
                <p class="text-secondary small mb-1"><i class="fa-solid fa-location-dot me-2 text-danger"></i> 123 Food Street, Tasty City</p>
                <p class="text-secondary small mb-1"><i class="fa-solid fa-phone me-2 text-danger"></i> +91 98765 43210</p>
                <p class="text-secondary small mb-3"><i class="fa-solid fa-envelope me-2 text-danger"></i> support@spicesfoods.com</p>
                <small class="text-white d-block">Open Hours: 11:00 AM - 11:00 PM</small>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <!-- Copyright -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center text-secondary small">
            <p class="mb-0">&copy; <?= date('Y') ?> Spices Foods. All Rights Reserved.</p>
            <p class="mb-0">Pure Vegetarian Food Delivery</p>
        </div>
    </div>
</footer>
