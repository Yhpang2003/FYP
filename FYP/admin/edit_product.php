<?php
require_once '../_base.php';
require_once '../_head.php';

require_admin();

// --- Global variables ---
global $_err;
$product = null;

// --- 1. Get Product ID and Fetch Existing Data ---
$id = get('id');
if (!$id || !is_numeric($id)) {
    flash('error', 'Invalid product ID.');
    redirect('edit_product.php');
}

$conn = db_connect();
// Prepare a statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM Product WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// If no product was found, redirect back with an error
if (!$product) {
    flash('error', 'Product not found.');
    redirect('edit_product.php');
}


// --- 2. Handle Form Submission (POST Request) ---
if (is_post()) {
    // Get and sanitize form data
    $name = post('name', $product['name']);
    $description = post('description', $product['description']);
    $price = post('price', $product['price']);
    $stock_quantity = post('stock_quantity', $product['stock_quantity']);
    $category_id = post('category_id', $product['category_id']);
    $image = get_file('image'); // Check for a new image upload

    // Validate the submitted data
    if (!$name) $_err['name'] = 'Product name is required.';
    if (!$price || !is_money($price)) $_err['price'] = 'Please enter a valid price.';
    if (!$stock_quantity || !is_quantity($stock_quantity)) $_err['stock_quantity'] = 'Please enter a valid stock quantity.';
    if (!$category_id) $_err['category_id'] = 'Please select a category.';

    // If validation passes, proceed with the update
    if (empty($_err)) {
        $db_image_path = $product['image']; // Default to the old image path

        // If a new image was uploaded, process it
        if ($image) {
            // Save the new photo and get its filename
            $new_image_filename = save_photo($image, '../uploads/product_images');
            $db_image_path = 'uploads/product_images/' . $new_image_filename;

            // Delete the old image file if it exists
            if ($product['image'] && file_exists('../' . $product['image'])) {
                unlink('../' . $product['image']);
            }
        }

        // Prepare the UPDATE statement
        $stmt = $conn->prepare(
            "UPDATE Product SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, image = ? WHERE product_id = ?"
        );
        $stmt->bind_param("ssdiisi", $name, $description, $price, $stock_quantity, $category_id, $db_image_path, $id);

        if ($stmt->execute()) {
            flash('success', 'Product updated successfully!');
            redirect('manage_product.php');
        } else {
            flash('error', 'Database error: ' . $stmt->error);
            redirect(); // Redirect back to the edit form on error
        }
        $stmt->close();
    }
}

// --- 3. Pre-load categories for the dropdown ---
$category_result = $conn->query("SELECT * FROM Category ORDER BY category_name");
$categories = $category_result->fetch_all(MYSQLI_ASSOC);
$conn->close();

?>

<div class="w-full max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Edit Product: <?= e($product['name']) ?></h1>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Product Name:</label>
            <input type="text" id="name" name="name" value="<?= e(post('name', $product['name'])) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
            <?php err('name'); ?>
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
            <textarea id="description" name="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700"><?= e(post('description', $product['description'])) ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price:</label>
                <input type="text" id="price" name="price" value="<?= e(post('price', $product['price'])) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                <?php err('price'); ?>
            </div>
            <div>
                <label for="stock_quantity" class="block text-gray-700 text-sm font-bold mb-2">Stock Quantity:</label>
                <input type="number" id="stock_quantity" name="stock_quantity" value="<?= e(post('stock_quantity', $product['stock_quantity'])) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                <?php err('stock_quantity'); ?>
            </div>
        </div>

        <div class="mb-4">
            <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
            <select id="category_id" name="category_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                <option value="">-- Select a Category --</option>
                <?php foreach ($categories as $category): ?>
                    <?php $selected_category = post('category_id', $product['category_id']); ?>
                    <option value="<?= e($category['category_id']) ?>" <?= $selected_category == $category['category_id'] ? 'selected' : '' ?>>
                        <?= e($category['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php err('category_id'); ?>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Current Image:</label>
            <?php if ($product['image']): ?>
                <img src="../<?= e($product['image']) ?>" alt="Current product image" class="w-32 h-32 object-cover rounded mb-2">
            <?php else: ?>
                <p class="text-gray-500 italic">No image available.</p>
            <?php endif; ?>
            <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Upload New Image (Optional):</label>
            <input type="file" id="image" name="image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Save Changes
            </button>
            <a href="manage_product.php" class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-gray-800">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php
require_once '../_foot.php';
?>
