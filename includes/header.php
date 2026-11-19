<?php
// Include functions and DB setup
require_once __DIR__ . '/functions.php';

// Default page title fallback
$pageTitle = $pageTitle ?? SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <!-- Bright Light Aesthetic Red Custom Palette Override -->
    <style>
        html, body {
            margin: 0 !important;
            padding: 0 !important;
        }

        :root {
            --aesthetic-bg-light: #FAFAFA;       /* Bright Crisp Light Background */
            --aesthetic-card-bg: #FFFFFF;        /* Pure White Card Fill */
            --aesthetic-red-primary: #8B0000;    /* Deep Crimson Headings */
            --aesthetic-red-accent: #C41E3A;     /* Soft Rose Accent */
            --aesthetic-red-light: #FDF2F2;      /* Pale Blush Highlight */
            --aesthetic-red-border: #F3D2D2;     /* Subtle Light Red Border */
            --aesthetic-text-main: #1F2937;      /* High Contrast Dark Charcoal Text */
            --aesthetic-text-muted: #6B7280;     /* Soft Gray Subtitles */
        }

        body {
            background-color: var(--aesthetic-bg-light) !important;
            color: var(--aesthetic-text-main) !important;
        }

        /* Card Customization */
        .card {
            background-color: var(--aesthetic-card-bg) !important;
            border: 1px solid var(--aesthetic-red-border) !important;
            box-shadow: 0 4px 12px rgba(139, 0, 0, 0.04) !important;
            border-radius: 12px !important;
        }

        /* Buttons Overrides */
        .btn-primary, .btn-aesthetic-red {
            background-color: var(--aesthetic-red-accent) !important;
            border-color: var(--aesthetic-red-accent) !important;
            color: #ffffff !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover, .btn-aesthetic-red:hover {
            background-color: var(--aesthetic-red-primary) !important;
            border-color: var(--aesthetic-red-primary) !important;
            box-shadow: 0 4px 12px rgba(196, 30, 58, 0.25);
        }

        .btn-outline-primary, .btn-outline-aesthetic-red {
            border-color: var(--aesthetic-red-accent) !important;
            color: var(--aesthetic-red-accent) !important;
            background-color: transparent;
        }

        .btn-outline-primary:hover, .btn-outline-aesthetic-red:hover {
            background-color: var(--aesthetic-red-accent) !important;
            color: #ffffff !important;
        }

        /* Text Utilities */
        .text-primary, .brand-accent {
            color: var(--aesthetic-red-primary) !important;
        }

        /* Alert Messages */
        .alert-danger {
            background-color: #FDE8E8 !important;
            color: #9B1C1C !important;
            border: 1px solid #F8B4B4 !important;
        }

        .alert-success {
            background-color: #EDF7ED !important;
            color: #1E4620 !important;
            border: 1px solid #C3E6CB !important;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<!-- Global Flash Message Display -->
<div class="container mt-3">
    <?= displayFlash(); ?>
</div>