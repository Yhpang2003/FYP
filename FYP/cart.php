<?php
require_once '_base.php';

require_auth('Member');

// Get the logged-in user's ID
$user_id = $_user->user_id;
$conn = db_connect();

// --- HANDLE ADD TO CART (POST REQUEST) ---
if (is_post() && post('product_id')) { // Check if it's an 'add to cart' post
    $product_id = post('product_id');
    $quantity = (int)post('quantity');

    if ($product_id && $quantity > 0) {
        // 1. Find the user's active cart
        $stmt = $conn->prepare("SELECT cart_id FROM Cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $cart = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $cart_id = null;
        if ($cart) {
            $cart_id = $cart['cart_id'];
        } else {
            // No cart exists, create one
            $stmt = $conn->prepare("INSERT INTO Cart (user_id) VALUES (?)");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $cart_id = $stmt->insert_id;
            $stmt->close();
        }

        // 2. Check stock quantity
        $stmt = $conn->prepare("SELECT stock_quantity FROM Product WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product_stock = $stmt->get_result()->fetch_assoc()['stock_quantity'];
        $stmt->close();

        // 3. Check if item is already in cart
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
            // 4. Add or update the item in the cart
            if ($existing_item) {
                $stmt = $conn->prepare("UPDATE Cart_Item SET quantity = ? WHERE cart_id = ? AND product_id = ?");
                $stmt->bind_param("iii", $new_total_quantity, $cart_id, $product_id);
            } else {
                $stmt = $conn->prepare("INSERT INTO Cart_Item (cart_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $cart_id, $product_id, $quantity);
            }
            $stmt->execute();
            $stmt->close();
            flash('success', 'Product added to cart!');
        }
    } else {
        flash('error', 'Invalid product or quantity.');
    }
    
    
    redirect('cart.php');
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
                <!-- Cart Items -->
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
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <span class="text-gray-700">Qty: <?= e($item['quantity']) ?></span>
                                    <span class="text-xl font-bold text-gray-800">RM<?= e(number_format($item_total_price, 2)) ?></span>
                                    <a href="remove_from_cart.php?id=<?= e($item['product_id']) ?>" class="text-red-500 hover:text-red-700 font-semibold">Remove</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:w-1/3">
                    <div class="bg-white p-6 rounded-lg shadow-md sticky top-8">
                        <h2 class="text-2xl font-bold text-gray-800 border-b pb-4 mb-4">Order Summary</h2>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700">Subtotal</span>
                            
                            <span id="cart-subtotal" class="text-gray-800 font-semibold">RM<?= e(number_format($total_price, 2)) ?></span>
                        </div>
                        <div class="flex justify-between mb-4">
                            <span class="text-gray-700">Shipping</span>
                            <span class="text-gray-800 font-semibold">TBD</span>
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

<!-- NEW: JavaScript for dynamic total calculation -->
<script>
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

        subtotalEl.textContent = `RM${newTotal.toFixed(2)}`;
        totalEl.textContent = `RM${newTotal.toFixed(2)}`;
    }

    checkboxes.forEach(box => {
        box.addEventListener('change', updateTotals);
    });

    // Run once on page load to set the initial correct total
    updateTotals();
});
</script>

<?php
require_once '_foot.php';
?>

