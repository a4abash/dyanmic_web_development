<?php
// cart.php
require 'config/auth.php';
require_once 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

/** Helpers */
function redirect_with($params = []) {
    $base = 'cart.php';
    if (!empty($params)) $base .= '?' . http_build_query($params);
    header("Location: {$base}");
    exit;
}
function money($n) { return number_format((float)$n, 2); }

/** Actions */
$action = $_GET['action'] ?? '';
try {
    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));

        // Validate product from DB
        $stmt = $conn->prepare("SELECT id, name, price, stock, image FROM products WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $prod = $stmt->get_result()->fetch_assoc();

        if (!$prod) {
            redirect_with(['error' => 'Product not found']);
        }

        // Cap qty to stock
        $qty = min($qty, (int)$prod['stock']);

        // Merge/increment if exists
        if (isset($_SESSION['cart'][$pid])) {
            $newQty = $_SESSION['cart'][$pid]['qty'] + $qty;
            $_SESSION['cart'][$pid]['qty'] = min($newQty, (int)$prod['stock']);
        } else {
            $_SESSION['cart'][$pid] = [
                'id'    => (int)$prod['id'],
                'name'  => $prod['name'],
                'price' => (float)$prod['price'],
                'qty'   => $qty,
                'image' => $prod['image'],
                'stock' => (int)$prod['stock'],
            ];
        }
        redirect_with(['updated' => 1]);
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Bulk quantity update
        foreach ($_POST['qty'] ?? [] as $pid => $q) {
            $pid = (int)$pid;
            $q   = max(1, (int)$q);

            if (isset($_SESSION['cart'][$pid])) {
                $q = min($q, (int)$_SESSION['cart'][$pid]['stock']);
                $_SESSION['cart'][$pid]['qty'] = $q;
            }
        }
        redirect_with(['updated' => 1]);
    }

    if ($action === 'remove' && isset($_GET['id'])) {
        $pid = (int)$_GET['id'];
        unset($_SESSION['cart'][$pid]);
        redirect_with(['updated' => 1]);
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        redirect_with(['updated' => 1]);
    }
} catch (Throwable $e) {
    error_log("Cart error: " . $e->getMessage());
    redirect_with(['error' => 'Cart error']);
}

/** Compute totals */
$items = array_values($_SESSION['cart']);
$subtotal = 0.0;
foreach ($items as $it) {
    $subtotal += $it['price'] * $it['qty'];
}
$shipping = 0.00; // flat free shipping
$total = $subtotal + $shipping;
?>

<title>Shopping Cart - Ripper Tech & Solutions</title>

<style>
.cart-wrap {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 24px;
    margin: 24px auto;
    max-width: 1100px;
}

.cart-card,
.summary-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
    padding: 16px 18px;
}

.cart-title {
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.cart-items {
    width: 100%;
    border-collapse: collapse;
}

.cart-items th,
.cart-items td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.cart-items th {
    text-align: left;
    color: #556;
}

.cart-thumb {
    width: 70px;
    height: 70px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid #eee;
}

.qty-input {
    width: 72px;
    padding: 6px;
    text-align: center;
}

.text-right {
    text-align: right;
}

.muted {
    color: #6c757d;
    font-size: .9rem;
}

.actions-row {
    display: flex;
    gap: 10px;
    align-items: center;
}

.summary-card h3 {
    font-size: 1.25rem;
    margin-bottom: 10px;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.summary-total {
    font-weight: 700;
    font-size: 1.1rem;
    border-top: 1px solid #eee;
    padding-top: 10px;
    margin-top: 8px;
}

.btn {
    display: inline-block;
    border: none;
    padding: 10px 14px;
    border-radius: 8px;
    cursor: pointer;
}

.btn-primary {
    background: #0d6efd;
    color: #fff;
}

.btn-danger {
    background: #dc3545;
    color: #fff;
}

.btn-outline {
    background: transparent;
    border: 1px solid #ddd;
    color: #333;
}

.btn-link {
    background: transparent;
    color: #0d6efd;
    text-decoration: underline;
    padding: 0;
}

.alert {
    margin: 10px 0 0;
    padding: 10px 12px;
    border-radius: 8px;
}

.alert-success {
    background: #e7f6ec;
    color: #256c3b;
}

.alert-danger {
    background: #fdeaea;
    color: #842029;
}

@media (max-width: 900px) {
    .cart-wrap {
        grid-template-columns: 1fr;
    }
}
</style>

<section class="cart-wrap">

    <div class="cart-card" role="region" aria-label="Shopping cart items">
        <div class="cart-title">🛒 Shopping Cart</div>

        <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success" role="alert">Cart updated.</div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
        <p class="muted">Your cart is empty.</p>
        <a class="btn btn-outline" href="products.php">← Continue Shopping</a>
        <?php else: ?>
        <form method="post" action="cart.php?action=update">
            <table class="cart-items">
                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">Price</th>
                        <th scope="col">Qty</th>
                        <th scope="col" class="text-right">Subtotal</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                    <tr>
                        <td>
                            <div style="display:flex; gap:12px; align-items:center;">
                                <img class="cart-thumb" src="uploads/<?= htmlspecialchars($it['image']) ?>"
                                    alt="<?= htmlspecialchars($it['name']) ?>">
                                <div>
                                    <div><strong><?= htmlspecialchars($it['name']) ?></strong></div>
                                    <div class="muted">In stock: <?= (int)$it['stock'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td>$<?= money($it['price']) ?></td>
                        <td>
                            <label for="q_<?= (int)$it['id'] ?>" class="visually-hidden">Quantity for
                                <?= htmlspecialchars($it['name']) ?></label>
                            <input id="q_<?= (int)$it['id'] ?>" class="qty-input" type="number" min="1"
                                max="<?= (int)$it['stock'] ?>" name="qty[<?= (int)$it['id'] ?>]"
                                value="<?= (int)$it['qty'] ?>">
                        </td>
                        <td class="text-right">$<?= money($it['price'] * $it['qty']) ?></td>
                        <td class="actions-row">
                            <a class="btn btn-link" href="cart.php?action=remove&id=<?= (int)$it['id'] ?>"
                                aria-label="Remove <?= htmlspecialchars($it['name']) ?>">Remove</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="display:flex; justify-content:space-between; margin-top:14px;">
                <a class="btn btn-outline" href="products.php">← Continue Shopping</a>
                <div>
                    <a class="btn btn-danger" href="cart.php?action=clear"
                        onclick="return confirm('Clear all items from cart?')">Clear Cart</a>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <aside class="summary-card" role="region" aria-label="Order summary">
        <h3>Order Summary</h3>

        <div class="summary-line"><span>Subtotal</span><strong>$<?= money($subtotal) ?></strong></div>
        <div class="summary-line">
            <span>Shipping</span><strong><?= ($shipping == 0 ? 'Free' : '$' . money($shipping)) ?></strong></div>
        <div class="summary-line summary-total"><span>Total</span><strong>$<?= money($total) ?></strong></div>

        <div style="margin-top: 14px;">
            <button class="btn btn-primary" type="button" onclick="alert('Checkout flow not implemented yet')">Proceed
                to Checkout</button>
        </div>
    </aside>

</section>

<?php include 'includes/footer.php'; ?>