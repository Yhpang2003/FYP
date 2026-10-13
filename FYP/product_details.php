<?php
require_once '_base.php';
require_once '_head.php';

// --- 1. Get Product ID and Fetch Data ---
$id = get('id');
if (!$id || !is_numeric($id)) {
    // Redirect to the main products page if ID is invalid
    redirect('products.php');
}

$conn = db_connect();
$stmt = $conn->prepare(
    "SELECT p.*, c.category_name 
     FROM Product p 
     LEFT JOIN Category c ON p.category_id = c.category_id 
     WHERE p.product_id = ? AND p.status = 'Available'"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();
$conn->close();

// If no product was found (or it's disabled), redirect away
if (!$product) {
    flash('error', 'The product you are looking for does not exist or is unavailable.');
    redirect('products.php');
}
?>

<div class="container mx-auto px-4 py-8">
    
    <?php flash_messages();?>

    <div class="bg-white p-8 rounded-lg shadow-md flex flex-col md:flex-row">
        
        <!-- Product Image -->
        <div class="md:w-1/2 mb-6 md:mb-0">
            <?php if (!empty($product['image'])): ?>
                <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" class="w-full max-h-[500px] object-contain rounded-lg mx-auto">
            <?php else: ?>
                <div class="w-full h-96 bg-gray-200 flex items-center justify-center text-gray-400 rounded-lg">No Image Available</div>
            <?php endif; ?>
        </div>
        
        <!-- Product Details & Actions -->
        <div class="md:w-1/2 md:pl-8">
            <span class="text-sm text-gray-500"><?= e($product['category_name'] ?? 'Uncategorized') ?></span>
            <h1 class="text-3xl font-bold text-gray-900 mt-1"><?= e($product['name']) ?></h1>
            
            <p class="text-3xl font-bold text-blue-600 my-4">RM<?= e(number_format($product['price'], 2)) ?></p>
            
            <div class="mb-4">
                <span class="font-semibold">Availability:</span>
                <?php if ($product['stock_quantity'] > 0): ?>
                    <span class="text-green-600 ml-2"><?= e($product['stock_quantity']) ?> in stock</span>
                <?php else: ?>
                    <span class="text-red-600 ml-2">Out of Stock</span>
                <?php endif; ?>
            </div>

            <p class="text-gray-700 leading-relaxed mb-6">
                <?= nl2br(e($product['description'])) ?>
            </p>

            <?php if (is_auth() && !is_admin()): ?>
                <?php if ($product['stock_quantity'] > 0): ?>
                    <!-- Add to Cart Form -->
                    <form action="cart.php" method="POST">
                        <!-- Hidden input to send the product ID -->
                        <input type="hidden" name="product_id" value="<?= e($product['product_id']) ?>">
                        
                        <div class="flex items-center mb-4">
                            <label for="quantity" class="mr-4 font-semibold">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= e($product['stock_quantity']) ?>" 
                                   class="w-20 p-2 border border-gray-300 rounded-md text-center">
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-300">
                            Add to Cart
                        </button>
                    </form>
                <?php else: ?>
                    <button type="button" class="w-full bg-gray-400 text-white font-bold py-3 px-4 rounded-lg cursor-not-allowed" disabled>
                        Out of Stock
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <!-- Show login prompt if user is a guest -->
                <div class="border-t pt-6 mt-6">
                    <p class="text-gray-700 text-lg">You must be logged in as member to add items to your cart.</p>
                    <a href="/FYP/login.php" class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg">
                        Login or Register
                    </a>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<?php
require_once '_foot.php';
?>

