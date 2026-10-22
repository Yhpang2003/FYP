<?php
require_once '_base.php';
require_once '_head.php';

$conn = db_connect();

// --- 1. CONFIGURATION: Pagination Setup ---
$limit = 30; // Number of products per page
$page  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // Current page (default 1)
$offset = ($page - 1) * $limit; // Calculate starting point

// --- 2. Get Filters ---
$search = get('search');
$category_id = get('category_id');
$min_price = get('min_price');
$max_price = get('max_price');

// --- 3. Fetch Categories ---
$category_result = $conn->query("SELECT * FROM Category ORDER BY category_name");
$categories = $category_result->fetch_all(MYSQLI_ASSOC);

// --- 4. Build Dynamic WHERE Clause ---
// We build the conditions separately so we can reuse them for both COUNT and SELECT queries
$where_clauses = ["p.status = 'Available'"];
$params = [];
$types = "";

if ($search) {
    $where_clauses[] = "p.name LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}
if ($category_id) {
    $where_clauses[] = "p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}
if ($min_price) {
    $where_clauses[] = "p.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}
if ($max_price) {
    $where_clauses[] = "p.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

$where_sql = " WHERE " . implode(" AND ", $where_clauses);

// --- 5. QUERY A: Get Total Count (For Pagination) ---
$count_sql = "SELECT COUNT(*) FROM Product p " . $where_sql;
$stmt_count = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_items = $stmt_count->get_result()->fetch_row()[0];
$stmt_count->close();

$total_pages = ceil($total_items / $limit); // Calculate total pages needed

// --- 6. QUERY B: Get Actual Products (With LIMIT) ---
$sql = "SELECT p.*, c.category_name 
        FROM Product p 
        LEFT JOIN Category c ON p.category_id = c.category_id" 
        . $where_sql 
        . " ORDER BY p.name ASC LIMIT ?, ?";

// Add pagination params to the existing filter params
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// --- Helper: Function to keep search filters in URL when changing pages ---
function get_url($page_num) {
    $query = $_GET;
    $query['page'] = $page_num;
    return '?' . http_build_query($query);
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Our Products</h1>
        <span class="text-gray-500 text-sm">Showing <?= count($products) ?> of <?= $total_items ?> items</span>
    </div>

    <!-- --- Filter Form --- -->
    <form method="GET" action="products.php" class="bg-white p-6 rounded-lg shadow-md mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Product Name" class="mt-1 block w-full rounded border-gray-300 p-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category_id" class="mt-1 block w-full rounded border-gray-300 p-2 border">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat['category_id']) ?>" <?= $category_id == $cat['category_id'] ? 'selected' : '' ?>>
                            <?= e($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Min Price</label>
                <input type="number" name="min_price" value="<?= e($min_price) ?>" placeholder="0" class="mt-1 block w-full rounded border-gray-300 p-2 border">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Max Price</label>
                <input type="number" name="max_price" value="<?= e($max_price) ?>" placeholder="1000" class="mt-1 block w-full rounded border-gray-300 p-2 border">
            </div>
        </div>
        <div class="flex justify-end gap-4 mt-4">
            <a href="products.php" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Reset</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Apply Filters</button>
        </div>
    </form>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
        <?php foreach ($products as $product): ?>
            <a href="product_details.php?id=<?= e($product['product_id']) ?>" class="block bg-white rounded-lg shadow hover:shadow-xl transition duration-300 overflow-hidden group">
                <div class="w-full h-48 bg-gray-200 relative">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-400">No Image</div>
                    <?php endif; ?>
                </div>
                <div class="p-4">
                    <p class="text-xs text-gray-500 mb-1"><?= e($product['category_name'] ?? 'Uncategorized') ?></p>
                    <h2 class="text-sm font-semibold text-gray-900 truncate mb-1"><?= e($product['name']) ?></h2>
                    <p class="text-lg font-bold text-blue-600">RM<?= number_format($product['price'], 2) ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- No Results -->
    <?php if (empty($products)): ?>
        <div class="text-center py-12">
            <p class="text-gray-500 text-xl">No products found.</p>
            <a href="products.php" class="text-blue-600 hover:underline mt-2 inline-block">View all products</a>
        </div>
    <?php endif; ?>

    <!-- --- PAGINATION LINKS --- -->
    <?php if ($total_pages > 1): ?>
    <div class="flex justify-center mt-10">
        <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            
            <!-- Previous Button -->
            <?php if ($page > 1): ?>
                <a href="<?= get_url($page - 1) ?>" class="px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 rounded-l-md">
                    Previous
                </a>
            <?php endif; ?>

            <!-- Page Numbers -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="<?= get_url($i) ?>" 
                   class="px-4 py-2 border border-gray-300 text-sm font-medium 
                   <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' ?>">
                   <?= $i ?>
                </a>
            <?php endfor; ?>

            <!-- Next Button -->
            <?php if ($page < $total_pages): ?>
                <a href="<?= get_url($page + 1) ?>" class="px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 rounded-r-md">
                    Next
                </a>
            <?php endif; ?>
            
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php require_once '_foot.php'; ?>