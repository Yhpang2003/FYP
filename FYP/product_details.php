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



// If no product was found
if (!$product) {
    flash('error', 'The product you are looking for does not exist or is unavailable.');
    redirect('products.php');
}


// Record user activity
$current_user_id = 0;
if (is_auth()&& !is_admin()) {
    $user_id = $_user->user_id;
    $stmt_log = $conn->prepare("INSERT INTO Browse_History (user_id, product_id, activity_type) VALUES (?, ?, 'view')");
    $stmt_log->bind_param("ii", $user_id, $id);
    $stmt_log->execute();
    $stmt_log->close();
}

//Get Recommendations
$recommended_products = [];
if (!is_admin()) { // *** FIX: Only run recommendation logic if NOT admin ***
    $current_user_id = (is_auth()) ? $_user->user_id : 0;

    $command = 'python recommend.py ' . escapeshellarg($id) . ' ' . escapeshellarg($current_user_id);
    $output = shell_exec($command);
    $recommended_ids = json_decode($output, true);

    if (!empty($recommended_ids)) {
        $ids_string = implode(',', array_map('intval', $recommended_ids));
        
        // Fetch product details for the recommended IDs
        $sql_rec = "SELECT product_id, name, price, image, category_id 
                    FROM Product 
                    WHERE product_id IN ($ids_string) AND status = 'Available'
                    ORDER BY FIELD(product_id, $ids_string)
                    LIMIT 6"; 
                    
        $result_rec = $conn->query($sql_rec);
        if ($result_rec) {
            $recommended_products = $result_rec->fetch_all(MYSQLI_ASSOC);
        }
    }
}


$conn->close();
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

    <?php if (!empty($recommended_products) && !is_admin()): ?>
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b pb-2">Recommended for You</h2>
        

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
            <?php foreach ($recommended_products as $rec_product): ?>
                <a href="product_details.php?id=<?= e($rec_product['product_id']) ?>" 
                   class="block bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden group">
                    <div class="w-full h-40 bg-gray-200">
                        <?php if (!empty($rec_product['image'])): ?>
                            <img src="/FYP/<?= e($rec_product['image']) ?>" alt="<?= e($rec_product['name']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-400">No Image</div>
                        <?php endif; ?>
                    </div>
                    <div class="p-3">
                        <!-- Truncate text slightly more aggressively for smaller cards -->
                        <h3 class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-600" title="<?= e($rec_product['name']) ?>">
                            <?= e($rec_product['name']) ?>
                        </h3>
                        <p class="text-base font-bold text-gray-800 mt-1">RM<?= e(number_format($rec_product['price'], 2)) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
require_once '_foot.php';
?>

