<?php
require_once '_base.php';


// A 'guest' check. If user is already logged in, redirect them away
guest();

global $_err; 

// Handle form submission
if (is_post()) {
    $username = post('username');
    $email = post('email');
    $password = post('password');
    $confirm_password = post('confirm_password');

    // Validation
    if (!$username) $_err['username'] = 'Username is required.';
    if (!$email) {
        $_err['email'] = 'Email is required.';
    } else if (!is_email($email)) {
        $_err['email'] = 'Please enter a valid email address.';
    } else if (!is_unique($email, 'User', 'email')) {
        $_err['email'] = 'This email is already taken. Please try another.';
    }
    if (strlen($password) < 8) $_err['password'] = 'Password must be at least 8 characters long.';
    if ($password !== $confirm_password) $_err['confirm_password'] = 'Passwords do not match.';

    // If no errors, create the user
    if (empty($_err)) {
        $conn = db_connect();
        
        // --- NEW ACTIVATION LOGIC ---
        // 1. Generate a unique activation token
        $activation_token = bin2hex(random_bytes(32)); // 64-character token
        
        // 2. Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert user with 'Inactive' status and the token
        $stmt = $conn->prepare(
            "INSERT INTO User (username, email, password, role, status, reset_token) 
             VALUES (?, ?, ?, 'Member', 'Inactive', ?)"
        );
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $activation_token);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();

            // 4. Send the activation email
            $activation_link = "http://localhost/FYP/activate.php?token=" . $activation_token;
            
            $email_subject = "Activate Your DIY-Store Account";
            $email_body = "<h1>Welcome to the E-Commerce, " . e($username) . "!</h1>";
            $email_body .= "<p>Thank you for registering. Please click the link below to activate your account:</p>";
            $email_body .= "<a href='$activation_link' style='display:inline-block; padding: 12px 20px; background-color: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px;'>Activate Your Account</a>";
            $email_body .= "<p>If you cannot click the link, please copy and paste this URL into your browser:</p>";
            $email_body .= "<p>$activation_link</p>";

            if (send_email($email, $username, $email_subject, $email_body)) {
                flash('success', 'Registration successful! Please check your email to activate your account.');
                redirect('login.php');
            } else {
                // This is tricky - user is in DB but email failed.
                // For a real app, you might have a "resend activation" page.
                // For now, we'll show an error.
                flash('error', 'Registration succeeded, but we could not send an activation email. Please contact support.');
                redirect('login.php');
            }

        } else {
            flash('error', 'An error occurred during registration. Please try again.');
            $stmt->close();
            $conn->close();
        }
    }
}

// --- All logic is done, now include head ---
require_once '_head.php';
?>

<div class="w-full max-w-md mx-auto bg-white p-8 rounded-lg shadow-md mt-12">
    <h1 class="text-2xl font-bold mb-6 text-center">Create Your Account</h1>

    <?php flash_messages(); // Show any flash messages (success or error) ?>

    <form method="POST" action="register.php" novalidate>
        <div class="mb-4">
            <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
            <input type="text" id="username" name="username" value="<?= e(post('username')) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?= isset($_err['username']) ? 'border-red-500' : '' ?>">
            <?php err('username'); ?>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
            <input type="email" id="email" name="email" value="<?= e(post('email')) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?= isset($_err['email']) ? 'border-red-500' : '' ?>">
            <?php err('email'); ?>
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
            <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?= isset($_err['password']) ? 'border-red-500' : '' ?>">
            <?php err('password'); ?>
        </div>

        <div class="mb-6">
            <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline <?= isset($_err['confirm_password']) ? 'border-red-500' : '' ?>">
            <?php err('confirm_password'); ?>
        </div>

        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Register
        </button>

        <p class="text-center text-gray-600 text-sm mt-6">
            Already have an account? 
            <a href="/FYP/login.php" class="text-blue-500 hover:text-blue-800 font-bold">Log in here</a>
        </p>
    </form>
</div>

<?php
require_once '_foot.php';
?>

