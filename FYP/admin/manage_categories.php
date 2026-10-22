<?php
require_once '../_base.php';

require_admin();

global $_err;
$conn = db_connect();
$edit_category = null; // Variable to hold category data for editing

// --- Handle Form Submissions (Add or Edit) ---
if (is_post()) {
    $action = post('action');
    $category_name = post('category_name');
    $category_id = post('category_id');

    if (empty($category_name)) {
        $_err['category_name'] = 'Category name is required.';
    } elseif (!is_unique($category_name, 'Category', 'category_name') && $action === 'add') {
         $_err['category_name'] = 'This category name already exists.';
    }
    
    if (empty($_err)) {
        if ($action === 'add') {
            // --- Add New Category ---
            $stmt = $conn->prepare("INSERT INTO Category (category_name) VALUES (?)");
            $stmt->bind_param("s", $category_name);
            if ($stmt->execute()) {
                flash('success', 'Category added successfully.');
            } else {
                flash('error', 'Error adding category: ' . $stmt->error);
            }
            $stmt->close();
            
        } elseif ($action === 'edit') {
            // --- Update Existing Category ---
            $stmt = $conn->prepare("UPDATE Category SET category_name = ? WHERE category_id = ?");
            $stmt->bind_param("si", $category_name, $category_id);
            if ($stmt->execute()) {
                flash('success', 'Category updated successfully.');
            } else {
                flash('error', 'Error updating category: ' . $stmt->error);
            }
            $stmt->close();
        }
        redirect('manage_categories.php'); // Redirect to clear form
    }
}

// --- Handle Delete Action ---
if (get('action') === 'delete' && get('id')) {
    $category_id = get('id');
    
    // Check if any products are using this category
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM Product WHERE category_id = ?");
    $stmt_check->bind_param("i", $category_id);
    $stmt_check->execute();
    $count = $stmt_check->get_result()->fetch_row()[0];
    $stmt_check->close();

    if ($count > 0) {
        flash('error', 'Cannot delete category. It is still being used by ' . $count . ' product(s).');
    } else {
        $stmt_del = $conn->prepare("DELETE FROM Category WHERE category_id = ?");
        $stmt_del->bind_param("i", $category_id);
        if ($stmt_del->execute()) {
            flash('success', 'Category deleted successfully.');
        } else {
            flash('error', 'Error deleting category.');
        }
        $stmt_del->close();
    }
    redirect('manage_categories.php');
}

// --- Prepare Data for Page Load ---
// Check if we are in "edit" mode
if (get('action') === 'edit' && get('id')) {
    $category_id = get('id');
    $stmt_get = $conn->prepare("SELECT * FROM Category WHERE category_id = ?");
    $stmt_get->bind_param("i", $category_id);
    $stmt_get->execute();
    $edit_category = $stmt_get->get_result()->fetch_assoc();
    $stmt_get->close();
}

// Fetch all categories for the list
$result = $conn->query("SELECT * FROM Category ORDER BY category_id ASC");
$categories = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();


require_once '../_head.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Categories</h1>

    <?php flash_messages(); ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Add/Edit Form Card -->
        <div class="md:col-span-1">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">
                    <?= $edit_category ? 'Edit Category' : 'Add New Category' ?>
                </h2>
                
                <form action="manage_categories.php" method="POST">
                    <!-- Hidden fields for edit logic -->
                    <input type="hidden" name="action" value="<?= $edit_category ? 'edit' : 'add' ?>">
                    <input type="hidden" name="category_id" value="<?= e($edit_category['category_id'] ?? '') ?>">
                    
                    <div class="mb-4">
                        <label for="category_name" class="block text-gray-700 text-sm font-bold mb-2">Category Name:</label>
                        <input type="text" id="category_name" name="category_name" 
                               value="<?= e($edit_category['category_name'] ?? post('category_name')) ?>" 
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?= isset($_err['category_name']) ? 'border-red-500' : '' ?>"
                               required>
                        <?php err('category_name'); ?>
                    </div>

                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        <?= $edit_category ? 'Update Category' : 'Add Category' ?>
                    </button>
                    <?php if ($edit_category): ?>
                        <a href="manage_categories.php" class="block text-center w-full mt-2 text-sm text-gray-600 hover:text-gray-800">
                            Cancel Edit
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Category List Card -->
        <div class="md:col-span-2">
            <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Category ID</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Category Name</th>
                            <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4">No categories found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td class="text-left py-3 px-4"><?= e($category['category_id']) ?></td>
                                    <td class="text-left py-3 px-4 font-semibold"><?= e($category['category_name']) ?></td>
                                    <td class="text-left py-3 px-4">
                                        <a href="manage_categories.php?action=edit&id=<?= e($category['category_id']) ?>" class="text-blue-500 hover:text-blue-800 mr-4">Edit</a>
                                        <a href="manage_categories.php?action=delete&id=<?= e($category['category_id']) ?>" class="text-red-500 hover:text-red-800"
                                           onclick="return confirm('Are you sure you want to delete this category? This only works if no products are using it.');">
                                           Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>

<?php
require_once '../_foot.php';
?>