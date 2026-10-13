<?php
require_once '_base.php';

// Only guests (not logged-in users) can see this page
guest();

if (is_post()) {
    $email = post('email');
    
    if (!is_email($email)) {
        $_err['email'] = 'Please enter a valid email address.';
    } elseif (!is_exists($email, 'User', 'email')) {
        // SECURITY: Don't tell the user if the email exists.
        // Just show the same success message either way.
        // We only run the logic if the email *does* exist.
    } else {
        // --- Email exists, so generate a token ---
        $conn = db_connect();
        
        // 1. Generate a secure token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 3600); // Token expires in 1 hour

        // 2. Store the token in the database
        $stmt = $conn->prepare("UPDATE User SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expiry, $email);
        $stmt->execute();
        $stmt->close();
        $conn->close();

        // 3. Send the reset email
        $reset_link = "http://localhost/FYP/reset_password.php?token=" . $token;
        
        $subject = "Your Password Reset Link for DIY-Store";
        $body = "
            <h2>Password Reset Request</h2>
            <p>Someone (hopefully you) requested a password reset for your DIY-Store account.</p>
            <p>If this was not you, please ignore this email.</p>
            <p>To reset your password, click the link below (this link is valid for 1 hour):</p>
            <a href='$reset_link' style='background-color: #2563eb; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                Reset Your Password
            </a>
            <p>Or copy and paste this URL into your browser:</p>
            <p>$reset_link</p>
        ";
        
        send_email($email, $email, $subject, $body);
    }
    
    // Show a generic success message to prevent email enumeration
    flash('success', 'If an account with that email exists, a password reset link has been sent.');
    redirect('forgot_password.php');
}


require_once '_head.php';
?>

<div class="w-full max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6 text-center">Forgot Your Password?</h1>
    <p class="text-gray-600 text-center mb-6">Enter your email address and we will send you a link to reset your password.</p>
    
    <?php flash_messages(); ?>

    <form method="POST" action="forgot_password.php">
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address:</label>
            <input type="email" id="email" name="email" value="<?= e(post('email')) ?>"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?= isset($_err['email']) ? 'border-red-500' : '' ?>" required>
            <?php err('email'); ?>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Send Reset Link
            </button>
        </div>
        
        <div class="text-center mt-4">
            <a href="login.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Back to Login
            </a>
        </div>
    </form>
</div>

<?php
require_once '_foot.php';
?>
