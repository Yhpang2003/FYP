<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIY-Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.stripe.com/v3/"></script>
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
        <div class="container mx-auto px-4 max-w-7xl"> 
            
            <div class="flex items-center py-4 gap-8">
                
                <a href="/FYP/index.php" class="text-2xl font-bold text-gray-800 shrink-0">DIY-Store</a>
                
                <div class="hidden md:flex items-center space-x-6">
                    <a href="/FYP/products.php" class="text-gray-600 hover:text-blue-500 font-medium">Products</a>
                    <a href="/FYP/projects.php" class="text-gray-600 hover:text-blue-500 font-medium">DIY Projects</a>
                    
                    <?php if (is_admin()): // Show admin links if user is Admin ?>
                        <a href="/FYP/admin/manage_product.php" class="text-red-600 hover:text-red-800 font-medium">Manage Products</a>
                        <a href="/FYP/admin/manage_projects.php" class="text-red-600 hover:text-red-800 font-medium">Manage Projects</a>
                        <a href="/FYP/admin/report.php" class="text-red-600 hover:text-red-800 font-medium">Dashboard</a>
                    <?php endif; ?>
                </div>

                <div class="flex items-center space-x-4 ml-auto">
                    <?php if (is_auth()): ?>
                        
                        <span class="text-gray-700 text-sm hidden lg:inline">Welcome, <?= e($_user->username) ?>!</span> 
                        
                        <?php if (!is_admin()): ?>
                        <a href="/FYP/cart.php" class="text-gray-600 hover:text-blue-500 relative" title="Cart"> 
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </a>
                        <a href="/FYP/order_history.php" class="text-gray-600 hover:text-blue-500 text-sm font-medium hidden sm:block">Orders</a> 
                        <?php endif; ?>
                        
                        
                        <a href="/FYP/profile.php" class="text-gray-600 hover:text-blue-500 text-sm font-medium hidden sm:block">Profile</a> 
                        <a href="/FYP/logout.php" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded transition duration-200">Logout</a>
                    <?php else: ?>
                        <a href="/FYP/login.php" class="text-gray-600 hover:text-blue-500 font-medium">Login</a>
                        <a href="/FYP/register.php" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded transition duration-200">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Site content will go here -->

