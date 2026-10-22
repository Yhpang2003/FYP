<?php
require_once '_base.php';
require_once '_head.php';

$conn = db_connect();

// 1. Fetch 3 random product 
$sql_feat = "SELECT * FROM Product WHERE status = 'Available' ORDER BY RAND() LIMIT 3";
$result_feat = $conn->query($sql_feat);
$featured_products = $result_feat->fetch_all(MYSQLI_ASSOC);

// 2. Fetch 3 latest DIY Projects for the "Get Inspired" section
$sql_proj = "SELECT * FROM Project ORDER BY project_id DESC LIMIT 3";
$result_proj = $conn->query($sql_proj);
$latest_projects = $result_proj->fetch_all(MYSQLI_ASSOC);

$conn->close();

flash_messages();
?>
<!-- Main Section -->
<div class="bg-blue-600 text-white py-20">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-4">Build Your Dreams with DIY-Store</h1>
        <p class="text-xl md:text-2xl mb-8 text-blue-100">The ultimate destination for tools, materials, and project guides.</p>
        <div class="flex justify-center space-x-4">
            <a href="products.php" class="bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-full hover:bg-white hover:text-blue-600 transition duration-300">
                Shop Products
            </a>
            <a href="projects.php" class="bg-transparent border-2 border-white text-white font-bold py-3 px-8 rounded-full hover:bg-white hover:text-blue-600 transition duration-300">
                Explore Projects
            </a>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="container mx-auto px-4 py-16">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
        <div class="p-6 border rounded-lg shadow-sm hover:shadow-md transition">
            <div class="text-blue-600 text-4xl mb-4"><i class="fas fa-tools"></i></div>
            <h3 class="text-xl font-bold mb-2">Quality Tools</h3>
            <p class="text-gray-600">We stock only the best brands and durable tools for every job.</p>
        </div>
        <div class="p-6 border rounded-lg shadow-sm hover:shadow-md transition">
            <div class="text-green-600 text-4xl mb-4"><i class="fas fa-lightbulb"></i></div>
            <h3 class="text-xl font-bold mb-2">Project Guides</h3>
            <p class="text-gray-600">Step-by-step instructions to help you build amazing things.</p>
        </div>
        <div class="p-6 border rounded-lg shadow-sm hover:shadow-md transition">
            <div class="text-purple-600 text-4xl mb-4"><i class="fas fa-truck"></i></div>
            <h3 class="text-xl font-bold mb-2">Fast Delivery</h3>
            <p class="text-gray-600">Get your materials delivered straight to your doorstep.</p>
        </div>
    </div>
</div>

<!-- Featured Products -->
<div class="bg-gray-50 py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">Featured Products</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($featured_products as $prod): ?>
                <a href="product_details.php?id=<?= e($prod['product_id']) ?>" class="block bg-white rounded-lg shadow-md hover:shadow-xl transition transform hover:-translate-y-1 overflow-hidden group">
                    <div class="h-48 bg-gray-200 relative">
                        <?php if (!empty($prod['image'])): ?>
                            <!-- Display product image -->
                            <img src="/FYP/<?= e($prod['image']) ?>" alt="<?= e($prod['name']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-400">No Image</div>
                        <?php endif; ?>
                    </div>
                    <div class="p-6 text-center">
                        <h3 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 truncate"><?= e($prod['name']) ?></h3>
                        <p class="text-lg font-bold text-blue-500 mt-2">RM<?= e(number_format($prod['price'], 2)) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-10">
            <a href="products.php" class="text-blue-600 font-semibold hover:underline">View All Products &rarr;</a>
        </div>
    </div>
</div>

<!-- Latest Projects -->
<div class="container mx-auto px-4 py-16">
    <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">Get Inspired</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php foreach ($latest_projects as $proj): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="h-48 bg-gray-200">
                    <?php if (!empty($proj['project_image'])): ?>
                        <img src="<?= e($proj['project_image']) ?>" alt="<?= e($proj['project_name']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-400">No Image</div>
                    <?php endif; ?>
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2"><?= e($proj['project_name']) ?></h3>
                    <p class="text-gray-600 text-sm mb-4">
                        <?= substr(e($proj['description']), 0, 80) ?>...
                    </p>
                    <a href="project_details.php?id=<?= e($proj['project_id']) ?>" class="text-blue-600 font-semibold hover:underline">View Project</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-10">
        <a href="projects.php" class="bg-gray-800 text-white font-bold py-3 px-8 rounded-full hover:bg-gray-700 transition duration-300">
            View All Projects
        </a>
    </div>
</div>

<?php
require_once '_foot.php';
?>

