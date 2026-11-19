<?php
$pageTitle = "About Us - Spices Foods";
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<!-- Brightened Aesthetic Red Styling Override -->
<style>
    /* Scope background brightening for this content area */
    .about-page-wrapper {
        background-color: #FDF8F8; /* Soft, warm light blush */
        border-radius: 16px;
        padding: 3rem 2rem;
        box-shadow: 0 10px 30px rgba(139, 0, 0, 0.05);
        border: 1px solid rgba(196, 30, 58, 0.1);
    }

    .about-title {
        color: #8B0000 !important; /* Deep Rich Crimson */
    }

    .about-subtitle {
        color: #B22222 !important; /* Firebrick Accent */
        font-weight: 600;
    }

    .about-lead {
        color: #2C1810 !important; /* Dark Espresso for high legibility */
        font-weight: 500;
    }

    .about-text {
        color: #5A4A42 !important; /* Warm Soft Slate */
        line-height: 1.7;
    }

    .about-img-frame {
        border-radius: 12px;
        border: 4px solid #ffffff;
        box-shadow: 0 8px 24px rgba(139, 0, 0, 0.12) !important;
    }

    .feature-card {
        background-color: #ffffff;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid rgba(196, 30, 58, 0.12);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(139, 0, 0, 0.08);
    }

    .feature-icon {
        width: 48px;
        height: 48px;
        background-color: #FADBD8;
        color: #C41E3A;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1.25rem;
    }
</style>

<div class="container my-5">
    <div class="about-page-wrapper">
        <div class="row align-items-center g-5">
            <!-- Text Content -->
            <div class="col-lg-6">
                <span class="text-uppercase tracking-wider small about-subtitle">Our Heritage & Story</span>
                <h1 class="fw-bold my-2 about-title">About Spices Foods</h1>
                <p class="lead about-lead">Crafting authentic culinary experiences with hand-selected spices.</p>
                <p class="about-text">
                    Founded with a vision to deliver authentic, high-quality, spice-infused dishes straight to your home. Every item on our menu is prepared fresh daily with ethically sourced ingredients, traditional recipes, and pure passion.
                </p>

                <!-- Value Highlights -->
                <div class="row g-3 mt-3">
                    <div class="col-sm-6">
                        <div class="feature-card d-flex align-items-center gap-3">
                            <div class="feature-icon">
                                <i class="fa-solid fa-pepper-hot"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Natural Spices</h6>
                                <small class="text-muted">100% Pure & Fresh</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="feature-card d-flex align-items-center gap-3">
                            <div class="feature-icon">
                                <i class="fa-solid fa-leaf"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Pure Veg</h6>
                                <small class="text-muted">Hygienic & Authentic</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Block -->
            <div class="col-lg-6">
                <img src="assets/images/about.jpg" class="img-fluid about-img-frame" alt="About Spices Foods" onerror="this.src='https://via.placeholder.com/600x400/FADBD8/8B0000?text=Spices+Foods';">
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>