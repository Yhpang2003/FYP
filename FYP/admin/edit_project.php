<?php
require_once '../_base.php';

require_admin();

global $_err;
$project = null;

// 1. Get Project ID and Fetch Existing Data
$project_id = get('id');
if (!$project_id || !is_numeric($project_id)) {
    flash('error', 'Invalid project ID.');
    redirect('/FYP/admin/manage_projects.php');
}

$conn = db_connect();

// --- Handle form submission ---
if (is_post()) {
    // 2. Get and sanitize form data
    $project_name = post('project_name');
    $description = post('description');
    $image = get_file('project_image');

    // 3. Validation
    if (!$project_name) $_err['project_name'] = 'Project name is required.';
    
    // Image is optional, but if provided, check it
    if ($image && !in_array($image->type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
        $_err['project_image'] = 'Invalid file type. Please upload a JPG, PNG, GIF, or WEBP image.';
    }
    
    // 4. If no errors, proceed with update
    if (empty($_err)) {
        // Fetch old image path first
        $stmt_old = $conn->prepare("SELECT project_image FROM Project WHERE project_id = ?");
        $stmt_old->bind_param("i", $project_id);
        $stmt_old->execute();
        $old_image_path = $stmt_old->get_result()->fetch_assoc()['project_image'];
        $stmt_old->close();

        $db_image_path = $old_image_path; // Assume we're keeping the old image

        // --- Handle Image Upload ---
        if ($image) {
            $image_filename = save_photo($image, '../uploads/project_images'); 
            $db_image_path = 'uploads/project_images/' . $image_filename;
            
            // Delete the old one if it exists
            if ($old_image_path && file_exists('../' . $old_image_path)) {
                unlink('../' . $old_image_path);
            }
        }
        
        // --- Update Database ---
        $stmt = $conn->prepare("UPDATE Project SET project_name = ?, description = ?, project_image = ? WHERE project_id = ?");
        $stmt->bind_param("sssi", $project_name, $description, $db_image_path, $project_id);

        if ($stmt->execute()) {
            flash('success', 'Project updated successfully!');
            redirect('/FYP/admin/manage_projects.php');
        } else {
            flash('error', 'Database error: ' . $stmt->error);
        }
        $stmt->close();
    }
    
    // If validation fails, use POSTed values to refill form
    $project['project_name'] = post('project_name');
    $project['description'] = post('description');

} else {
    // --- Fetch existing data for GET request ---
    $stmt = $conn->prepare("SELECT * FROM Project WHERE project_id = ?");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();
    $stmt->close();
    
    if (!$project) {
        flash('error', 'Project not found.');
        redirect('/FYP/admin/manage_projects.php');
    }
}

$conn->close();

// --- All logic is done, now include head ---
require_once '../_head.php';
?>

<div class="w-full max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">Edit DIY Project</h1>
    
    <?php flash_messages(); ?>

    <form method="POST" action="edit_project.php?id=<?= e($project_id) ?>" enctype="multipart/form-data">
        <div class="mb-4">
            <label for="project_name" class="block text-gray-700 text-sm font-bold mb-2">Project Name:</label>
            <input type="text" id="project_name" name="project_name" value="<?= e($project['project_name']) ?>" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?= isset($_err['project_name']) ? 'border-red-500' : '' ?>">
            <?php err('project_name'); ?>
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
            <textarea id="description" name="description" rows="6" 
                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?= e($project['description']) ?></textarea>
        </div>

        <div class="mb-6">
            <label for="project_image" class="block text-gray-700 text-sm font-bold mb-2">Change Project Image (Optional):</label>
            <?php if (!empty($project['project_image']) && !is_post()): // Show current image only on initial load ?>
                <img src="../<?= e($project['project_image']) ?>" alt="Current Image" class="w-32 h-32 object-cover rounded-md mb-4">
                <p class="text-xs text-gray-600 mb-2">Current image. Upload a new file below to replace it.</p>
            <?php endif; ?>
            <input type="file" id="project_image" name="project_image" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <?php err('project_image'); ?>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Update Project
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

