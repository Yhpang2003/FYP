<?php
require_once '_base.php';
require_once '_head.php';

$conn = db_connect();

flash_messages();

// --- 1. Get filter inputs from the URL ---
$search = get('search');
$category_id = get('category_id');
$min_price = get('min_price');
$max_price = get('max_price');

// --- 2. Fetch all categories for the filter dropdown ---
$category_result = $conn->query("SELECT * FROM Category ORDER BY category_name");
$categories = $category_result->fetch_all(MYSQLI_ASSOC);

// --- 3. Dynamically build the SQL query ---
$sql = "SELECT p.*, c.category_name 
        FROM Product p 
        LEFT JOIN Category c ON p.category_id = c.category_id 
        WHERE p.status = 'Available'";
        
$params = [];
$types = "";

// Add conditions based on filters
if ($search) {
    $sql .= " AND p.name LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}
if ($category_id) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}
if ($min_price) {
    $sql .= " AND p.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}
if ($max_price) {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

$sql .= " ORDER BY p.name ASC";

// --- 4. Prepare and execute the query ---
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Our Products</h1>

    <!-- --- Filter and Search Form --- -->
    <form method="GET" action="products.php" class="bg-white p-6 rounded-lg shadow-md mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search Input -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search by Name</label>
                <input type="text" name="search" id="search" value="<?= e($search) ?>" placeholder="e.g., Hammer" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <!-- Category Select -->
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category_id" id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category['category_id']) ?>" <?= $category_id == $category['category_id'] ? 'selected' : '' ?>>
                            <?= e($category['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Price Range -->
            <div>
                <label for="min_price" class="block text-sm font-medium text-gray-700">Min Price</label>
                <input type="number" name="min_price" id="min_price" value="<?= e($min_price) ?>" placeholder="RM 0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="max_price" class="block text-sm font-medium text-gray-700">Max Price</label>
                <input type="number" name="max_price" id="max_price" value="<?= e($max_price) ?>" placeholder="RM 1000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>
        <div class="flex justify-end gap-4 mt-4">
            <a href="products.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Reset
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                Apply Filters
            </button>
        </div>
    </form>
    <!-- --- End of Filter Form --- -->


    <!-- Grid for Product Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 2xl:grid-cols-6 gap-4">
        <?php foreach ($products as $product): ?>
            <a href="product_details.php?id=<?= e($product['product_id']) ?>" class="block bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden group">

                <div class="w-full h-40 bg-gray-200">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>

                        <div class="w-full h-full flex items-center justify-center text-gray-400">No Image</div>
                    <?php endif; ?>
                </div>

                <div class="p-3">
                    <p class="text-xs text-gray-500 mb-1"><?= e($product['category_name'] ?? 'Uncategorized') ?></p>
                    <h2 class="text-base font-semibold text-gray-900 truncate group-hover:text-blue-600"><?= e($product['name']) ?></h2>
                    <p class="text-lg font-bold text-gray-800 mt-1">RM<?= e(number_format($product['price'], 2)) ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($products)): ?>
        <p class="text-center text-gray-500 mt-12 text-xl">No products match your search criteria.</p>
    <?php endif; ?>
</div>

<?php
require_once '_foot.php';
?>

