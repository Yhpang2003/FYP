<?php
require_once '../_base.php';


require_admin();

global $_err;

// --- Handle form submission ---
if (is_post()) {
    // 1. Get and sanitize form data
    $project_name = post('project_name');
    $description = post('description');
    $image = get_file('project_image');

    // 2. Validation
    if (!$project_name) $_err['project_name'] = 'Project name is required.';
    
    // Image is optional, but if provided, check it
    if ($image && !in_array($image->type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
        $_err['project_image'] = 'Invalid file type. Please upload a JPG, PNG, GIF, or WEBP image.';
    }

    // 3. If no errors, proceed
    if (empty($_err)) {
        $db_image_path = null; // Default to no image

        // --- Handle Image Upload ---
        if ($image) {
            // Use our existing save_photo function!
            // We'll save project images in their own folder
            $image_filename = save_photo($image, '../uploads/project_images'); 
            $db_image_path = 'uploads/project_images/' . $image_filename;
        }
        
        // --- Insert into Database ---
        $conn = db_connect();
        $stmt = $conn->prepare("INSERT INTO Project (project_name, description, project_image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $project_name, $description, $db_image_path);

        if ($stmt->execute()) {
            flash('success', 'Project added successfully!');
            redirect('/FYP/admin/manage_projects.php');
        } else {
            flash('error', 'Database error: ' . $stmt->error);
        }
        $stmt->close();
        $conn->close();
    }
}


require_once '../_head.php';
?>

<div class="w-full max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Add a New DIY Project</h1>
    
    <?php flash_messages(); ?>

    <form method="POST" action="add_project.php" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="project_name" class="block text-gray-700 text-sm font-bold mb-2">Project Name:</label>
            <input type="text" id="project_name" name="project_name" value="<?= e(post('project_name')) ?>" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?= isset($_err['project_name']) ? 'border-red-500' : '' ?>">
            <?php err('project_name'); ?>
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
            <textarea id="description" name="description" rows="6" 
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?= e(post('description')) ?></textarea>
        </div>

        <div class="mb-6">
            <label for="project_image" class="block text-gray-700 text-sm font-bold mb-2">Project Image (Optional):</label>
            <input type="file" id="project_image" name="project_image" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <?php err('project_image'); ?>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Add Project
            </button>
            <a href="manage_projects.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Back to Manage Projects
            </a>
        </div>
    </form>
</div>

<?php
require_once '../_foot.php';
?>

