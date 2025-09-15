<?php
require 'config/auth.php';
include 'includes/header.php';
require_once 'config/db.php';

try {
    $stmt = $conn->prepare("SELECT id, name, description, price, stock, image FROM products ORDER BY id DESC");
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
    $products = [];
}
?>
<title>Products - Ripper Tech & Solutions</title>

<section class="services-section">
    <h1>Our Products</h1>
    <p>List of Products:</p><br>

    <div class="service-grid">
        <?php if (empty($products)): ?>
        <div class="text-center text-muted py-4">
            <i class="fas fa-cart-plus fa-3x mb-3"></i>
            <h5>No Product Found</h5>
        </div>
        <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $p): ?>
            <div class="product-card" tabindex="0" role="region"
                aria-label="<?= htmlspecialchars($p['name']) ?> Product">
                <div class="product-image-wrapper">
                    <img src="uploads/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"
                        class="product-image">
                </div>
                <div class="product-details">
                    <h3 class="product-title"><?= htmlspecialchars($p['name']) ?></h3>
                    <p class="product-description"><?= htmlspecialchars($p['description']) ?></p>
                    <div class="product-meta">
                        <span class="product-price">$<?= number_format((float)$p['price'], 2) ?></span>
                        <span class="product-stock"><?= (int)$p['stock'] ?> in stock</span>
                    </div>

                    <form method="post" action="cart.php?action=add" class="cart-form">
                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                        <label for="qty_<?= (int)$p['id'] ?>" class="visually-hidden">Quantity</label>
                        <input id="qty_<?= (int)$p['id'] ?>" name="qty" type="number" min="1"
                            max="<?= (int)$p['stock'] ?>" value="1" required>
                        <button type="submit" class="btn-cart">🛒 Add to Cart</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>