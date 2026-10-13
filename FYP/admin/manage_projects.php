<?php
require_once '../_base.php';

require_admin();

// Handle "delete" action
if (get('action') === 'delete' && get('id')) {
    $project_id = get('id');
    $conn = db_connect();
    
    // NEW DELETE LOGIC 
    $conn->begin_transaction();
    try {
        // 1. Delete associated materials
        $stmt_mat = $conn->prepare("DELETE FROM Project_Material WHERE project_id = ?");
        $stmt_mat->bind_param("i", $project_id);
        $stmt_mat->execute();
        $stmt_mat->close();
        
        // 2. Delete associated steps
        $stmt_step = $conn->prepare("DELETE FROM Project_Step WHERE project_id = ?");
        $stmt_step->bind_param("i", $project_id);
        $stmt_step->execute();
        $stmt_step->close();

        // 3. Delete old image file if it exists
        $stmt_img = $conn->prepare("SELECT project_image FROM Project WHERE project_id = ?");
        $stmt_img->bind_param("i", $project_id);
        $stmt_img->execute();
        $result = $stmt_img->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['project_image'] && file_exists('../' . $row['project_image'])) {
                unlink('../' . $row['project_image']);
            }
        }
        $stmt_img->close();
        
        // 4. Now delete the project itself
        $stmt_proj = $conn->prepare("DELETE FROM Project WHERE project_id = ?");
        $stmt_proj->bind_param("i", $project_id);
        $stmt_proj->execute();
        $stmt_proj->close();
        
        // 5. Commit
        $conn->commit();
        flash('success', 'Project and all its content deleted successfully.');

    } catch (Exception $e) {
        $conn->rollback();
        flash('error', 'Error deleting project: ' . $e->getMessage());
    }
    // --- *** END NEW DELETE LOGIC *** ---
    
    $conn->close();
    redirect('manage_projects.php');
}


// Fetch all projects from the database
$conn = db_connect();
$sql = "SELECT * FROM Project ORDER BY project_id DESC";
$result = $conn->query($sql);
$projects = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();

// --- All logic is done, now include head ---
require_once '../_head.php';
?>

<div class="w-full bg-white p-8 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manage DIY Projects</h1>
        <a href="add_project.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            + Add New Project
        </a>
    </div>

    <?php flash_messages(); ?>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">ID</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Image</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Project Name</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if (empty($projects)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-4">No projects found. <a href="add_project.php" class="text-blue-500">Add one!</a></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <tr>
                            <td class="text-left py-3 px-4"><?= e($project['project_id']) ?></td>
                            <td class="text-left py-3 px-4">
                                <?php if (!empty($project['project_image'])): ?>
                                    <img src="../<?= e($project['project_image']) ?>" alt="<?= e($project['project_name']) ?>" class="h-12 w-12 object-cover rounded">
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-left py-3 px-4 font-semibold"><?= e($project['project_name']) ?></td>
                            <td class="text-left py-3 px-4">
                                <a href="manage_project_content.php?id=<?= e($project['project_id']) ?>" class="text-green-500 hover:text-green-800 mr-4 font-semibold">Manage Steps/Materials</a>
                                <a href="edit_project.php?id=<?= e($project['project_id']) ?>" class="text-blue-500 hover:text-blue-800 mr-4">Edit</a>
                                <a href="manage_projects.php?action=delete&id=<?= e($project['project_id']) ?>" class="text-red-500 hover:text-red-800" onclick="return confirm('Are you sure you want to delete this project? This will also delete all its steps and materials.');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once '../_foot.php';
?>