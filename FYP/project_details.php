<?php
require_once '_base.php';
// require_once '_head.php'; // Move head include after logic

$project_id = get('id');
if (!$project_id || !is_numeric($project_id)) {
    flash('error', 'Invalid project ID.');
    redirect('projects.php');
}

$conn = db_connect();

// 1. Fetch Project Details
$stmt = $conn->prepare("SELECT * FROM Project WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    flash('error', 'Project not found.');
    redirect('projects.php');
}

// 2. Fetch Project Steps
$steps_result = $conn->query("SELECT * FROM Project_Step WHERE project_id = $project_id ORDER BY step_number ASC");
$steps = $steps_result->fetch_all(MYSQLI_ASSOC);

// 3. Fetch Project Materials (Joined with Product table)
$sql_materials = "SELECT pm.*, p.name, p.price, p.image, p.stock_quantity 
                  FROM Project_Material pm
                  JOIN Product p ON pm.product_id = p.product_id
                  WHERE pm.project_id = $project_id";
$materials_result = $conn->query($sql_materials);
$materials = $materials_result->fetch_all(MYSQLI_ASSOC);

$conn->close();

// --- All logic is done, now include head ---
require_once '_head.php';
?>

<div class="container mx-auto px-4 py-8">
    <?php flash_messages(); ?>
    <div class="bg-white p-8 rounded-lg shadow-md">
        
        <!-- Project Header -->
        <div class="flex flex-col md:flex-row gap-8 border-b pb-6 mb-6">
            <div class="md:w-1/3">
                <?php if (!empty($project['project_image'])): ?>
                    <img src="<?= e($project['project_image']) ?>" alt="<?= e($project['project_name']) ?>" class="w-full h-auto object-cover rounded-lg shadow-md">
                <?php else: ?>
                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400 rounded-lg">No Image Available</div>
                <?php endif; ?>
            </div>
            <div class="md:w-2/3">
                <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= e($project['project_name']) ?></h1>
                <p class="text-gray-700 leading-relaxed">
                    <?= nl2br(e($project['description'])) ?>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Column 1: Project Steps -->
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Project Steps</h2>
                <div class="space-y-4">
                    <?php if (empty($steps)): ?>
                        <p class="text-gray-600">No steps have been added for this project yet.</p>
                    <?php else: ?>
                        <?php foreach ($steps as $step): ?>
                            <div class="flex items-start p-3 border rounded-md">
                                <span class="text-2xl font-bold text-blue-600 mr-4"><?= e($step['step_number']) ?>.</span>
                                <p class="text-gray-700 pt-1"><?= nl2br(e($step['step_description'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Column 2: Required Materials -->
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Required Materials</h2>
                <?php if (empty($materials)): ?>
                    <p class="text-gray-600">No materials listed for this project yet.</p>
                <?php else: ?>
                    
                   
                    
                    <div class="space-y-4">
                        <?php foreach ($materials as $material): ?>
                            <div class="flex items-center justify-between p-3 border rounded-md">
                                <div class="flex items-center">
                                    <img src="<?= e($material['image'] ?? 'https://placehold.co/100x100/e2e8f0/cbd5e0?text=No+Image') ?>" alt="<?= e($material['name']) ?>" class="w-16 h-16 object-cover rounded-md mr-4">
                                    <div>
                                        <a href="product_details.php?id=<?= e($material['product_id']) ?>" class="font-semibold text-gray-800 hover:text-blue-600"><?= e($material['name']) ?></a>
                                        <p class="text-sm text-gray-600">Required: <?= e($material['quantity']) ?></p>
                                        <p class="text-sm text-gray-600">
                                            (Stock: <?= e($material['stock_quantity']) ?>)
                                            <?php if ($material['quantity'] > $material['stock_quantity']): ?>
                                                <span class="text-red-500 font-semibold ml-2">Not enough stock!</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                <!-- Add single item to cart -->
                                <?php if ($material['stock_quantity'] > 0 && is_auth() && !is_admin()): ?>
                                    
                                    <form action="cart.php" method="POST" class="ml-4">
                                        <input type="hidden" name="product_id" value="<?= e($material['product_id']) ?>">
                                        <input type="hidden" name="quantity" value="<?= e($material['quantity']) ?>">
                                        <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold py-1 px-3 rounded-lg" title="Add <?= e($material['quantity']) ?> to cart">
                                            Add
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    
                    <form action="add_all.php" method="POST">
                        <input type="hidden" name="project_id" value="<?= e($project_id) ?>">
                        
                        <!-- Add all items to cart -->
                        <?php if (is_auth() && !is_admin()): ?>
                        <button type="submit" class="w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg">
                            Add All Materials to Cart
                        </button>
                        <?php elseif (!is_auth()): ?>
                         <a href="/FYP/login.php" class="block text-center w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg">
                            Login to Add Materials to Cart
                        </a>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php
require_once '_foot.php';
?>
