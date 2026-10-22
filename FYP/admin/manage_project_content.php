<?php
require_once '../_base.php';

require_admin();

global $_err;
$project_id = get('id');
$conn = db_connect();

// --- Validate Project ID ---
if (!$project_id || !is_numeric($project_id)) {
    flash('error', 'Invalid project ID.');
    redirect('manage_projects.php');
}

// --- Handle Form Submissions ---
if (is_post()) {
    $action = post('action');
    
    // --- Add a New Step ---
    if ($action === 'add_step') {
        $step_number = post('step_number');
        $step_description = post('step_description');
        
        if (!$step_number || !$step_description) {
            flash('error', 'Please fill in all step fields.');
        } else {
            $stmt = $conn->prepare("INSERT INTO Project_Step (project_id, step_number, step_description) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $project_id, $step_number, $step_description);
            if ($stmt->execute()) {
                flash('success', 'Step added successfully.');
            } else {
                flash('error', 'Error adding step: ' . $stmt->error);
            }
            $stmt->close();
        }
        redirect('manage_project_content.php?id=' . $project_id);
    }
    
    // --- Delete a Step ---
    if ($action === 'delete_step') {
        $step_id = post('step_id');
        $stmt = $conn->prepare("DELETE FROM Project_Step WHERE step_id = ? AND project_id = ?");
        $stmt->bind_param("ii", $step_id, $project_id);
        if ($stmt->execute()) {
            flash('success', 'Step deleted successfully.');
        } else {
            flash('error', 'Error deleting step.');
        }
        $stmt->close();
        redirect('manage_project_content.php?id=' . $project_id);
    }
    
    // --- Add a Material ---
    if ($action === 'add_material') {
        $product_id = post('product_id');
        $quantity = post('quantity');
        
        if (!$product_id || !$quantity || !is_numeric($quantity) || $quantity < 1) {
            flash('error', 'Please select a product and enter a valid quantity.');
        } else {
            // Check if material already exists to avoid duplicates
            $stmt_check = $conn->prepare("SELECT * FROM Project_Material WHERE project_id = ? AND product_id = ?");
            $stmt_check->bind_param("ii", $project_id, $product_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows > 0) {
                flash('error', 'That material is already added to this project.');
            } else {
                $stmt = $conn->prepare("INSERT INTO Project_Material (project_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $project_id, $product_id, $quantity);
                if ($stmt->execute()) {
                    flash('success', 'Material added successfully.');
                } else {
                    flash('error', 'Error adding material: ' . $stmt->error);
                }
                $stmt->close();
            }
            $stmt_check->close();
        }
        redirect('manage_project_content.php?id=' . $project_id);
    }
    
    // --- Delete a Material ---
    if ($action === 'delete_material') {
        $product_id = post('product_id');
        $stmt = $conn->prepare("DELETE FROM Project_Material WHERE project_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $project_id, $product_id);
        if ($stmt->execute()) {
            flash('success', 'Material removed successfully.');
        } else {
            flash('error', 'Error removing material.');
        }
        $stmt->close();
        redirect('manage_project_content.php?id=' . $project_id);
    }
}

// --- Fetch All Data for Page Load (GET request) ---

// 1. Fetch Project Details
$stmt = $conn->prepare("SELECT * FROM Project WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    flash('error', 'Project not found.');
    redirect('manage_projects.php');
}

// 2. Fetch Project Steps
$steps_result = $conn->query("SELECT * FROM Project_Step WHERE project_id = $project_id ORDER BY step_number ASC");
$steps = $steps_result->fetch_all(MYSQLI_ASSOC);

// 3. Fetch Project Materials (Joined with Product table)
$sql_materials = "SELECT pm.*, p.name, p.price 
                  FROM Project_Material pm
                  JOIN Product p ON pm.product_id = p.product_id
                  WHERE pm.project_id = $project_id";
$materials_result = $conn->query($sql_materials);
$materials = $materials_result->fetch_all(MYSQLI_ASSOC);

// 4. Fetch All Available Products 
$sql_products = "SELECT product_id, name, price FROM Product WHERE status = 'Available' ORDER BY name ASC";
$all_products_result = $conn->query($sql_products);
$all_products = $all_products_result->fetch_all(MYSQLI_ASSOC);

$conn->close();


require_once '../_head.php';
?>

<div class="container mx-auto px-4 py-8">
    
    <?php flash_messages(); ?>

    <a href="manage_projects.php" class="text-blue-500 hover:text-blue-700">&larr; Back to All Projects</a>
    <h1 class="text-3xl font-bold text-gray-900 mt-2 mb-6">Manage Content for: <?= e($project['project_name']) ?></h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- === PROJECT STEPS Column === -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Project Steps</h2>
            
            <!-- Form to Add New Step -->
            <form action="manage_project_content.php?id=<?= e($project_id) ?>" method="POST" class="mb-6 p-4 bg-gray-50 rounded-lg">
                <input type="hidden" name="action" value="add_step">
                <h3 class="text-lg font-semibold mb-2">Add New Step</h3>
                <div class="mb-4">
                    <label for="step_number" class="block text-gray-700 text-sm font-bold mb-2">Step Number:</label>
                    <input type="number" id="step_number" name="step_number" min="1" value="<?= count($steps) + 1 ?>"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label for="step_description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                    <textarea id="step_description" name="step_description" rows="3" 
                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                </div>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Add Step
                </button>
            </form>
            
            <!-- List of Existing Steps -->
            <div class="space-y-4">
                <?php if (empty($steps)): ?>
                    <p class="text-gray-600">No steps added yet.</p>
                <?php else: ?>
                    <?php foreach ($steps as $step): ?>
                        <div class="flex items-start justify-between p-3 border rounded-md">
                            <div class="flex items-start">
                                <span class="text-xl font-bold text-blue-600 mr-4"><?= e($step['step_number']) ?>.</span>
                                <p class="text-gray-700"><?= nl2br(e($step['step_description'])) ?></p>
                            </div>
                            <form action="manage_project_content.php?id=<?= e($project_id) ?>" method="POST" class="ml-4 flex-shrink-0">
                                <input type="hidden" name="action" value="delete_step">
                                <input type="hidden" name="step_id" value="<?= e($step['step_id']) ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm" onclick="return confirm('Are you sure you want to delete this step?');">
                                    Delete
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- === PROJECT MATERIALS Column === -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Required Materials</h2>
            
            <!-- Form to Add New Material -->
            <form action="manage_project_content.php?id=<?= e($project_id) ?>" method="POST" class="mb-6 p-4 bg-gray-50 rounded-lg">
                <input type="hidden" name="action" value="add_material">
                <h3 class="text-lg font-semibold mb-2">Add Material</h3>
                <div class="mb-4">
                    <label for="product_id" class="block text-gray-700 text-sm font-bold mb-2">Product:</label>
                    <select id="product_id" name="product_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="">-- Select a Product --</option>
                        
                        <?php foreach ($all_products as $product): ?>
                            <option value="<?= e($product['product_id']) ?>"><?= e($product['name']) ?> (RM<?= e(number_format($product['price'], 2)) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="quantity" class="block text-gray-700 text-sm font-bold mb-2">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="1" value="1"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Add Material
                </button>
            </form>
            
            <!-- List of Existing Materials -->
            <div class="space-y-4">
                <?php if (empty($materials)): ?>
                    <p class="text-gray-600">No materials added yet.</p>
                <?php else: ?>
                    <?php foreach ($materials as $material): ?>
                        <div class="flex items-center justify-between p-3 border rounded-md">
                            <div>
                                <p class="text-lg font-semibold text-gray-800"><?= e($material['name']) ?></p>
                                <p class="text-gray-600">Quantity: <?= e($material['quantity']) ?></p>
                            </div>
                            <form action="manage_project_content.php?id=<?= e($project_id) ?>" method="POST" class="ml-4">
                                <input type="hidden" name="action" value="delete_material">
                                <input type="hidden" name="product_id" value="<?= e($material['product_id']) ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm" onclick="return confirm('Are you sure you want to remove this material?');">
                                    Remove
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php
require_once '../_foot.php';
?>