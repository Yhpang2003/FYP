<?php
require_once '../_base.php'; // Path is correct
// Start by including the head
require_once '../_head.php';

require_admin();

// Global error array is now available from _base.php
global $_err; 

// --- Pre-loading data for the form ---
$conn = db_connect();
$category_result = $conn->query("SELECT * FROM Category ORDER BY category_name");
$categories = $category_result->fetch_all(MYSQLI_ASSOC);
$conn->close();


// --- Handle form submission ---
if (is_post()) {
    // 1. Get and sanitize form data using our new 'post' helper
    $name = post('name');
    $description = post('description');
    $price = post('price');
    $stock_quantity = post('stock_quantity');
    $category_id = post('category_id');
    $image = get_file('image'); // Use our new file helper

    // 2. Advanced Validation
    if (!$name) $_err['name'] = 'Product name is required.';
    if (!$price) {
        $_err['price'] = 'Price is required.';
    } else if (!is_money($price)) {
        $_err['price'] = 'Please enter a valid price (e.g., 10.50).';
    }
    if (!$stock_quantity) {
        $_err['stock_quantity'] = 'Stock quantity is required.';
    } else if (!is_quantity($stock_quantity)) {
        $_err['stock_quantity'] = 'Please enter a valid whole number for stock.';
    }
    if (!$category_id) $_err['category_id'] = 'Please select a category.';
    if (!$image) $_err['image'] = 'Product image is required.';


    // 3. If there are no errors, proceed with image upload and database insert
    if (empty($_err)) {

        $image_filename = save_photo($image, '../uploads/product_images'); 
        
        // The path to store in the database should be relative
        $db_image_path = 'uploads/product_images/' . $image_filename;

        $conn = db_connect();
        $stmt = $conn->prepare(
            "INSERT INTO Product (name, description, price, stock_quantity, category_id, image) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssdiis", $name, $description, $price, $stock_quantity, $category_id, $db_image_path);

        if ($stmt->execute()) {
            flash('success', 'Product added successfully!');
            redirect('manage_product.php'); 
        } else {
            flash('error', 'Database error: ' . $stmt->error);
        }
        $stmt->close();
        $conn->close();
        redirect(); 
    }
}
?>

<div class="w-full max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Add a New Product</h1>
    
    <?php flash_messages(); ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Product Name:</label>
            <input type="text" id="name" name="name" value="<?= e(post('name')) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <?php err('name'); ?>
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
            <textarea id="description" name="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?= e(post('description')) ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price:</label>
                <input type="text" id="price" name="price" value="<?= e(post('price')) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <?php err('price'); ?>
            </div>
            <div>
                <label for="stock_quantity" class="block text-gray-700 text-sm font-bold mb-2">Stock Quantity:</label>
                <input type="number" id="stock_quantity" name="stock_quantity" value="<?= e(post('stock_quantity')) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <?php err('stock_quantity'); ?>
            </div>
        </div>

        <div class="mb-4">
            <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
            <select id="category_id" name="category_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="">-- Select a Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e($category['category_id']) ?>" <?= post('category_id') == $category['category_id'] ? 'selected' : '' ?>>
                        <?= e($category['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php err('category_id'); ?>
        </div>

        <div class="mb-6">
            <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Product Image:</label>
            <input type="file" id="image" name="image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <?php err('image'); ?>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Add Product
            </button>
             
            <a href="manage_product.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Manage Products
            </a>
        </div>
    </form>
</div>

<?php
require_once '../_foot.php';
?>

