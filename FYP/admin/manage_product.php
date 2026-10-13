<?php
require_once '../_base.php';
require_admin(); 
require_once '../_head.php';

flash_messages(); 


$conn = db_connect();
// Join with Category table to get the category name
$sql = "SELECT p.*, c.category_name 
        FROM Product p 
        LEFT JOIN Category c ON p.category_id = c.category_id 
        ORDER BY p.product_id DESC";
$result = $conn->query($sql);
$products = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<div class="w-full bg-white p-8 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manage Products</h1>
        <a href="add_product.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            + Add New Product
        </a>
    </div>

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
            </tbody>
        </table>
    </div>
</div>

<?php
require_once '../_foot.php';
?>

