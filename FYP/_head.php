<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIY-Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/FYP/css/app.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-gray-100 font-sans">
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="/FYP/index.php" class="text-2xl font-bold text-gray-800">DIY-Store</a>
                
                <!-- Main Navigation -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="/FYP/products.php" class="text-gray-600 hover:text-blue-500">Products</a>
                    <a href="/FYP/projects.php" class="text-gray-600 hover:text-blue-500">DIY Projects</a>
                    
                    <?php if (is_admin()): // Show admin links if user is Admin ?>
                        <a href="/FYP/admin/manage_product.php" class="text-red-600 hover:text-red-800">Manage Products</a>
                        <a href="/FYP/admin/manage_projects.php" class="text-red-600 hover:text-red-800">Manage Projects</a>
                    <?php endif; ?>
                </div>

                <!-- Right-side Auth Links -->
                <div class="flex items-center space-x-4">
                    <?php if (is_auth()): ?>
                        
                        <span class="text-gray-700 text-sm hidden sm:inline">Welcome, <?= e($_user->username) ?>!</span> 
                        
                        <?php if (!is_admin()): ?>
                        <a href="/FYP/cart.php" class="text-gray-600 hover:text-blue-500" title="Cart"> 
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </a>
                        <?php endif; ?>
                        
                        <a href="/FYP/order_history.php" class="text-gray-600 hover:text-blue-500 text-sm sm:text-base">Order History</a> 
                        <a href="/FYP/profile.php" class="text-gray-600 hover:text-blue-500 text-sm sm:text-base">Profile</a> 
                        <a href="/FYP/logout.php" class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg">Logout</a>
                    <?php else: ?>
                        <a href="/FYP/login.php" class="text-gray-600 hover:text-blue-500">Login</a>
                        <a href="/FYP/register.php" class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="container mx-auto px-4 py-8">
        <!-- Site content will go here -->

