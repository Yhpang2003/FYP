<?php
require_once '_base.php';


$conn = db_connect();

// Fetch all projects from the database
$sql = "SELECT project_id, project_name, project_image, description FROM Project ORDER BY project_name ASC";
$result = $conn->query($sql);
$projects = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();


require_once '_head.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">DIY Projects</h1>

    <?php if (empty($projects)): ?>
        <p class="text-center text-gray-500 mt-12 text-xl">No projects have been added yet. Check back soon!</p>
    <?php else: ?>
        <!-- Grid for Project Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($projects as $project): ?>
                <a href="project_details.php?id=<?= e($project['project_id']) ?>" 
                   class="block bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden group">
                    
                    <!-- Project Image -->
                    <div class="w-full h-48 bg-gray-200">
                        <?php if (!empty($project['project_image'])): ?>
                            <img src="<?= e($project['project_image']) ?>" alt="<?= e($project['project_name']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-400">No Image</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Project Info -->
                    <div class="p-4">
                        <h2 class="text-xl font-semibold text-gray-900 truncate group-hover:text-blue-600"><?= e($project['project_name']) ?></h2>
                        <p class="text-gray-600 text-sm mt-2">
                            <?php 
                                $excerpt = substr($project['description'], 0, 100);
                                echo e($excerpt);
                                if (strlen($project['description']) > 100) echo '...';
                            ?>
                        </p>
                        <span class="inline-block text-blue-500 font-semibold mt-4 group-hover:underline">View Project &rarr;</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
require_once '_foot.php';
?>