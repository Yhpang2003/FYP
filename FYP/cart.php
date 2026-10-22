<?php
require_once '_base.php';

require_auth('Member');

// Get the logged-in user's ID
$user_id = $_user->user_id;
$conn = db_connect();

// --- HANDLE POST REQUESTS (ADD OR UPDATE) ---
if (is_post()) {
    
    // 1. HANDLE QUANTITY UPDATE (FROM CART PAGE)
    if (post('action') === 'update_qty') {
        $product_id = post('product_id');
        $change = (int)post('qty_change'); // Will be +1 or -1
        
        // Find Cart ID
        $stmt = $conn->prepare("SELECT cart_id FROM Cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $cart = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($cart) {
            $cart_id = $cart['cart_id'];

            // Get current quantity and stock
            $sql = "SELECT ci.quantity, p.stock_quantity 
                    FROM Cart_Item ci 
                    JOIN Product p ON ci.product_id = p.product_id 
                    WHERE ci.cart_id = ? AND ci.product_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $cart_id, $product_id);
            $stmt->execute();
            $item = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($item) {
                $new_qty = $item['quantity'] + $change;

                if ($new_qty <= 0) {
                    // Remove item if quantity becomes 0
                    $stmt = $conn->prepare("DELETE FROM Cart_Item WHERE cart_id = ? AND product_id = ?");
                    $stmt->bind_param("ii", $cart_id, $product_id);
                    $stmt->execute();
                    $stmt->close();
                } elseif ($new_qty > $item['stock_quantity']) {
                    flash('error', 'Max stock reached for this item.');
                } else {
                    // Update Quantity
                    $stmt = $conn->prepare("UPDATE Cart_Item SET quantity = ? WHERE cart_id = ? AND product_id = ?");
                    $stmt->bind_param("iii", $new_qty, $cart_id, $product_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
        redirect('cart.php'); // Reload to see changes
    }

    // 2. HANDLE ADD TO CART (FROM PRODUCT PAGE)
    elseif (post('product_id') && post('quantity')) { 
        $product_id = post('product_id');
        $quantity = (int)post('quantity');

        if ($product_id && $quantity > 0) {
            // Find or Create Cart
            $stmt = $conn->prepare("SELECT cart_id FROM Cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $cart = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $cart_id = null;
            if ($cart) {
                $cart_id = $cart['cart_id'];
            } else {
                $stmt = $conn->prepare("INSERT INTO Cart (user_id) VALUES (?)");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $cart_id = $stmt->insert_id;
                $stmt->close();
            }

            // Check stock
            $stmt = $conn->prepare("SELECT stock_quantity FROM Product WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product_stock = $stmt->get_result()->fetch_assoc()['stock_quantity'];
            $stmt->close();

            // Check existing cart item
            $stmt = $conn->prepare("SELECT quantity FROM Cart_Item WHERE cart_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $cart_id, $product_id);
            $stmt->execute();
            $existing_item = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $current_quantity_in_cart = $existing_item ? $existing_item['quantity'] : 0;
            $new_total_quantity = $current_quantity_in_cart + $quantity;

            if ($new_total_quantity > $product_stock) {
                flash('error', 'Not enough stock available for this item.');
            } else {
                if ($existing_item) {
                    $stmt = $conn->prepare("UPDATE Cart_Item SET quantity = ? WHERE cart_id = ? AND product_id = ?");
                    $stmt->bind_param("iii", $new_total_quantity, $cart_id, $product_id);
                } else {
                    $stmt = $conn->prepare("INSERT INTO Cart_Item (cart_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->bind_param("iii", $cart_id, $product_id, $quantity);
                }
                $stmt->execute();
                $stmt->close();

                // Log activity
                $stmt_log = $conn->prepare("INSERT INTO Browse_History (user_id, product_id, activity_type) VALUES (?, ?, 'add_to_cart')");
                $stmt_log->bind_param("ii", $user_id, $product_id);
                $stmt_log->execute();
                $stmt_log->close();

                flash('success', 'Product added to cart!');
            }
        } else {
            flash('error', 'Invalid product or quantity.');
        }
        redirect('cart.php');
    }
}

// --- DISPLAY THE CART (GET REQUEST) ---
$stmt = $conn->prepare("SELECT cart_id FROM Cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();
$stmt->close();

$cart_items = [];
$total_price = 0;

if ($cart) {
    $cart_id = $cart['cart_id'];
    $sql = "SELECT p.product_id, p.name, p.price, p.image, p.stock_quantity, ci.quantity 
            FROM Cart_Item ci
            JOIN Product p ON ci.product_id = p.product_id
            WHERE ci.cart_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($item = $result->fetch_assoc()) {
        $cart_items[] = $item;
        $total_price += $item['price'] * $item['quantity']; 
    }
    $stmt->close();
}
$conn->close();

require_once '_head.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Your Shopping Cart</h1>
    
    <?php flash_messages();?>

    <?php if (empty($cart_items)): ?>
        <div class="bg-white p-8 rounded-lg shadow-md text-center">
            <p class="text-xl text-gray-700">Your cart is empty.</p>
            <a href="/FYP/products.php" class="inline-block mt-6 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg">
                Continue Shopping
            </a>
        </div>
    <?php else: ?>
        
        <form id="cart-form" action="/FYP/checkout.php" method="POST">
            <div class="flex flex-col lg:flex-row gap-8">
                <div class="lg:w-2/3">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <?php foreach ($cart_items as $item): 
                            $item_total_price = $item['price'] * $item['quantity'];
                        ?>
                            <div class="flex flex-col sm:flex-row items-center justify-between py-4 border-b">
                                <div class="flex items-center mb-4 sm:mb-0">
                                    
                                    <input type="checkbox" name="selected_items[]" value="<?= e($item['product_id']) ?>" 
                                           class="cart-item-checkbox h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-4"
                                           data-price="<?= e($item_total_price) ?>" checked>

                                    <img src="/FYP/<?= e($item['image'] ?? 'https://placehold.co/100x100/e2e8f0/cbd5e0?text=No+Image') ?>" alt="<?= e($item['name']) ?>" class="w-24 h-24 object-cover rounded-md mr-4">
                                    <div>
                                        <a href="product_details.php?id=<?= e($item['product_id']) ?>" class="text-lg font-semibold text-gray-800 hover:text-blue-600"><?= e($item['name']) ?></a>
                                        <p class="text-gray-600">RM<?= e(number_format($item['price'], 2)) ?></p>
                                        <p class="text-xs text-gray-500">Stock: <?= e($item['stock_quantity']) ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    
                                    <div class="flex items-center border border-gray-300 rounded">
                                        <button type="submit" 
                                                formaction="cart.php" 
                                                name="qty_change" 
                                                value="-1"
                                                onclick="addHiddenField(this, '<?= $item['product_id'] ?>', 'update_qty')"
                                                class="px-3 py-1 text-gray-600 hover:bg-gray-100 border-r border-gray-300 transition">
                                            -
                                        </button>
                                        
                                        <span class="px-4 py-1 text-gray-800 font-medium"><?= e($item['quantity']) ?></span>
                                        
                                        <button type="submit" 
                                                formaction="cart.php"
                                                name="qty_change" 
                                                value="1" 
                                                onclick="addHiddenField(this, '<?= $item['product_id'] ?>', 'update_qty')"
                                                class="px-3 py-1 text-gray-600 hover:bg-gray-100 border-l border-gray-300 transition">
                                            +
                                        </button>
                                    </div>
                                    <span class="text-xl font-bold text-gray-800 w-24 text-right">RM<?= e(number_format($item_total_price, 2)) ?></span>
                                    <a href="remove_from_cart.php?id=<?= e($item['product_id']) ?>" class="text-red-500 hover:text-red-700 font-semibold ml-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="lg:w-1/3">
                    <div class="bg-white p-6 rounded-lg shadow-md sticky top-8">
                        <h2 class="text-2xl font-bold text-gray-800 border-b pb-4 mb-4">Order Summary</h2>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700">Subtotal</span>
                            <span id="cart-subtotal" class="text-gray-800 font-semibold">RM<?= e(number_format($total_price, 2)) ?></span>
                        </div>
                        <div class="flex justify-between text-xl font-bold border-t pt-4 mt-4">
                            <span>Total</span>
                            <span id="cart-total">RM<?= e(number_format($total_price, 2)) ?></span>
                        </div>
                        
                        <button type="submit" class="block text-center w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg">
                            Proceed to Checkout
                        </button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
// Helper function to inject the product ID and action when clicking +/-
function addHiddenField(btn, productId, action) {
    const form = btn.closest('form');
    
    // Clear old hidden inputs if they exist (to prevent duplicates)
    const oldId = form.querySelector('input[name="product_id"]');
    const oldAction = form.querySelector('input[name="action"]');
    if(oldId) oldId.remove();
    if(oldAction) oldAction.remove();

    // Add Product ID
    const inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'product_id';
    inputId.value = productId;
    form.appendChild(inputId);

    // Add Action Type
    const inputAction = document.createElement('input');
    inputAction.type = 'hidden';
    inputAction.name = 'action';
    inputAction.value = action;
    form.appendChild(inputAction);
}

document.addEventListener('DOMContentLoaded', () => {
    const checkboxes = document.querySelectorAll('.cart-item-checkbox');
    const subtotalEl = document.getElementById('cart-subtotal');
    const totalEl = document.getElementById('cart-total');

    function updateTotals() {
        let newTotal = 0;
        checkboxes.forEach(box => {
            if (box.checked) {
                newTotal += parseFloat(box.dataset.price);
            }
        });

        const formatted = new Intl.NumberFormat('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(newTotal);
        subtotalEl.textContent = `RM${formatted}`;
        totalEl.textContent = `RM${formatted}`;
    }

    checkboxes.forEach(box => {
        box.addEventListener('change', updateTotals);
    });

    updateTotals();
});
</script>

<?php
require_once '_foot.php';
?>