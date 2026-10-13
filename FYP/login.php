<?php
require_once '_base.php';
require_once '_head.php';

// A 'guest' check. If user is already logged in, redirect them away
guest();

global $_err;

// Handle form submission
if (is_post()) {
    $email = post('email');
    $password = post('password');

    // Validation
    if (!$email) $_err['email'] = 'Email is required.';
    if (!$password) $_err['password'] = 'Password is required.';

    if (empty($_err)) {
        $conn = db_connect();
        $stmt = $conn->prepare("SELECT * FROM User WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Verify user exists and password is correct
        if ($user && password_verify($password, $user['password'])) {
            
            // Check if user account is active
            if ($user['status'] === 'Inactive') {
                flash('error', 'Your account is inactive. Please contact support.');
            } else {
                // Password is correct! Log the user in.
                login($user); 
                
                // Redirect admin to dashboard, member to products
                if (is_admin()) {
                    redirect('/FYP/admin/manage_product.php');
                } else {
                    redirect('/FYP/products.php');
                }
            }
        } else {
            // Invalid credentials
            flash('error', 'Invalid email or password.');
        }
        $stmt->close();
        $conn->close();
    }
}
?>

<div class="w-full max-w-md mx-auto bg-white p-8 rounded-lg shadow-md mt-12">
    <h1 class="text-2xl font-bold mb-6 text-center">Login to Your Account</h1>

    <?php flash_messages(); // Show any flash messages (success or error) ?>

    <form method="POST" action="">
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
            <input type="email" id="email" name="email" value="<?= e(post('email')) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <?php err('email'); ?>
        </div>

        <div class="mb-6">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
            <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <?php err('password'); ?>
        </div>

        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Login
        </button>

        <p class="text-center text-gray-600 text-sm mt-6">
            Don't have an account? 
            <a href="register.php" class="text-blue-500 hover:text-blue-800 font-bold">Register here</a>
        </p>
        <p class="text-center text-gray-600 text-sm mt-2">
            <a href="forgot_password.php" class="text-blue-500 hover:text-blue-800">Forgot your password?</a>
        </p>
    </form>
</div>

<?php
require_once '_foot.php';
?>
