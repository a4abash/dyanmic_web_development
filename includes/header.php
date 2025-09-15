<?php
session_start(); // ensure session is started

// Defaults; pages can override
$pageTitle = $pageTitle ?? 'Ripper Tech & Solutions';
$pageDesc  = $pageDesc  ?? 'Web, mobile, cloud and security services for SMEs.';
$canonical = $canonical ?? ('https://YOURDOMAIN.tld' . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$isPrivate = $isPrivate ?? false; // set true in login/admin to noindex
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Ripper Teach & Solutions - Web & App Development, cloud Solutions, and Security Services.">
    <meta name="keywords" content="Ripper Tech & Solutions, Web Development, App Development, Cloud Solutions, Security Services">
    <title>Ripper Tech & Solutions - Home</title>
    <link rel="icon" type="image/png" href="assets/images/logo-2.png">
    <link rel="stylesheet" href="assets/css/style.css" />
    <link href="assets/css/css2.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>



</head>

<body>
    <header>
        <nav class="navbar" role="navigation" aria-label="Main Navigation Bar">
            <div class="logo"><a href="index.html"><img src="assets/images/logo-2.png" alt="Logo for Ripper Tech & Solutions" class="thumbnail"></a>
            </div>
            <label for="menu-toggle" class="menu-icon"><i class="fas fa-bars"></i></label>
            <ul class="nav-links">
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="services.php">Services</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="media.php">Gallery</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Show Logout if user is logged in -->
                    <li><a class="logout" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <!-- Show Login if not logged in -->
                    <li><a class="login" href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>

        </nav>
    </header>
    <main>
</body>

    <a class="skip-link" href="#main">Skip to main content</a>
<nav class="navbar" role="navigation" aria-label="Main Navigation">
  <!-- your list items -->
</nav>
<main id="main">
