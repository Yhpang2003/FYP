<?php
require_once '_base.php';

// Only guests (not logged-in users) can see this page
guest();

// 1. Get token from URL
$token = get('token');
if (empty($token)) {
    flash('error', 'Invalid or missing reset token.');
    redirect('login.php');
}

// 2. Find user with this token and check expiry
$conn = db_connect();
$stmt = $conn->prepare("SELECT user_id, email FROM User WHERE reset_token = ? AND reset_token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user) {
    // Token is invalid or expired
    flash('error', 'Your password reset link is invalid or has expired.');
    redirect('login.php');
}

// 3. Handle the new password submission
if (is_post()) {
    $password = post('password');
    $confirm_password = post('confirm_password');

    if (strlen($password) < 8) {
        $_err['password'] = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $_err['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($_err)) {
        // All good, update the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $conn = db_connect();
        // Update password and clear the reset token so it can't be used again
        $stmt = $conn->prepare("UPDATE User SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_password, $user['user_id']);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        
        flash('success', 'Your password has been reset successfully! You can now log in.');
        redirect('login.php');
    }
}


require_once '_head.php';
?>

<div class="w-full max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6 text-center">Reset Your Password</h1>
    <p class="text-gray-600 text-center mb-6">Enter a new password for your account (<?= e($user['email']) ?>).</p>
    
    <?php flash_messages(); ?>

    <form method="POST" action="reset_password.php?token=<?= e($token) ?>">
        <div class="mb-4">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">New Password:</label>
            <input type="password" id="password" name="password" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline <?= isset($_err['password']) ? 'border-red-500' : '' ?>" required>
            <?php err('password'); ?>
        </div>

        <div class="mb-6">
            <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline <?= isset($_err['confirm_password']) ? 'border-red-500' : '' ?>" required>
            <?php err('confirm_password'); ?>
        </div>

        <div class="flex items-center justify-center">
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Reset Password
            </button>
        </div>
    </form>
</div>

<?php
require_once '_foot.php';
?>
