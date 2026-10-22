<?php
require_once '../_base.php';
require_admin(); 
require_once '../_head.php';
require_once '../lib/SimplePager.php'; // Include the pager library

flash_messages(); 

$conn = db_connect();

// --- 1. Get Search and Filter Inputs ---
$search = get('search');
$category_id = get('category_id');
$page = get('page', 1); // Default to page 1

// --- 2. Build the SQL Query with Filters ---
// Start with a base query
$sql = "SELECT p.*, c.category_name 
        FROM Product p 
        LEFT JOIN Category c ON p.category_id = c.category_id 
        WHERE 1=1"; // 1=1 allows us to easily append AND conditions

$params = [];
$types = "";

// Add search condition
if ($search) {
    $sql .= " AND p.name LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

// Add category filter
if ($category_id) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

$sql .= " ORDER BY p.product_id DESC";

$limit = 10; // Products per page
$offset = ($page - 1) * $limit;

// Get total count for pagination
$count_sql = "SELECT COUNT(*) FROM Product p WHERE 1=1";
if ($search) $count_sql .= " AND p.name LIKE '$search%'"; // Simplified for count
if ($category_id) $count_sql .= " AND p.category_id = $category_id";

$count_result = $conn->query($count_sql);
// Fetch total count using a prepared statement for security
$sql_count = "SELECT COUNT(*) FROM Product p WHERE 1=1";
if ($search) $sql_count .= " AND p.name LIKE ?";
if ($category_id) $sql_count .= " AND p.category_id = ?";

$stmt_count = $conn->prepare($sql_count);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_items = $stmt_count->get_result()->fetch_row()[0];
$stmt_count->close();

$total_pages = ceil($total_items / $limit);

// Add Limit to the main query
$sql .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

// Execute main query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch categories for the filter dropdown
$cat_result = $conn->query("SELECT * FROM Category ORDER BY category_name");
$categories = $cat_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<div class="w-full bg-white p-8 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manage Products</h1>
        <div class="flex space-x-4">
            <a href="manage_categories.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Manage Categories
            </a>
            <a href="add_product.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                 Add New Product
            </a>
        </div>
    </div>

    <!-- --- Search and Filter Form --- -->
    <form method="GET" action="manage_product.php" class="mb-6 flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by product name..." 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="w-full md:w-1/4">
            <select name="category_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat['category_id']) ?>" <?= $category_id == $cat['category_id'] ? 'selected' : '' ?>>
                        <?= e($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Filter
            </button>
            <a href="manage_product.php" class="text-gray-600 hover:text-gray-800 ml-2 text-sm">Reset</a>
        </div>
    </form>


    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Image</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Name</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Category</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Price</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Stock</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Status</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-gray-500">No products found matching your criteria.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="text-left py-3 px-4"><?= e($product['product_id']) ?></td>
                            <td class="text-left py-3 px-4">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="../<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>" class="h-12 w-12 object-cover rounded">
                                <?php else: ?>
                                    <span class="text-gray-400">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-left py-3 px-4 font-semibold"><?= e($product['name']) ?></td>
                            <td class="text-left py-3 px-4"><?= e($product['category_name'] ?? 'N/A') ?></td>
                            <td class="text-left py-3 px-4">RM<?= e($product['price']) ?></td>
                            <td class="text-left py-3 px-4"><?= e($product['stock_quantity']) ?></td>
                            <td class="text-left py-3 px-4">
                                <span class="px-2 py-1 font-semibold leading-tight rounded-full
                                    <?= $product['status'] === 'Available' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                    <?= e($product['status']) ?>
                                </span>
                            </td>
                            <td class="text-left py-3 px-4">
                                <a href="edit_product.php?id=<?= e($product['product_id']) ?>" class="text-blue-500 hover:text-blue-800 mr-4">Edit</a>
                                
                                <?php if ($product['status'] === 'Available'): ?>
                                    <a href="toggle_product_status.php?id=<?= e($product['product_id']) ?>" 
                                       class="text-red-500 hover:text-red-800"
                                       onclick="return confirm('Are you sure you want to hide this product?')">Hide</a>
                                <?php else: ?>
                                    <a href="toggle_product_status.php?id=<?= e($product['product_id']) ?>" 
                                       class="text-green-500 hover:text-green-800"
                                       onclick="return confirm('Are you sure you want to make this product available?')">Enable</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- --- Pagination Links --- -->
    <?php if ($total_pages > 1): ?>
        <div class="mt-6 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <!-- Previous Page Link -->
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= e($search) ?>&category_id=<?= e($category_id) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span>Previous</span>
                    </a>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= e($search) ?>&category_id=<?= e($category_id) ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium 
                       <?= $i == $page ? 'text-blue-600 bg-blue-50 border-blue-500 z-10' : 'text-gray-700 hover:bg-gray-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <!-- Next Page Link -->
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= e($search) ?>&category_id=<?= e($category_id) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span>Next</span>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>

</div>

<?php
require_once '../_foot.php';
?>