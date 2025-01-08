<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the OTP entered by the user
    $otp_verify = $_POST['otp'];

    // Check if the entered OTP matches the one in session
    if ($otp_verify == $_SESSION['otp']) {
        // OTP is correct, proceed with user login or other actions
        $email = $_SESSION['email'];

        // Clean up the session
        session_unset();
        session_destroy();

        // Database logic
        require "../dbh.inc.php";
        $query = "SELECT * FROM users WHERE email=:email";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result) {
            // Set user session
            $user_id = $result['unique_id'];
            session_start();
            $_SESSION['unique_id'] = $user_id;
            // Redirect to profile page
            header('Location: ../profile');
            exit();
        }
    } else {
        // If OTP doesn't match
        $error = "Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="form.css">
</head>

<body>
    <div class="container">
        <div class="form-container">
            <h2>Verify OTP</h2>
            <form action="" method="POST" id="otpForm">
                <div class="form-group">
                    <input type="text" name="otp" id="otp" placeholder="Enter OTP" required>
                </div>

                <button type="submit">Verify OTP</button>

                <?php if (isset($error)): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>

                <p>If you didn't receive an OTP, <a href="../login_otp">click here to resend</a></p>
            </form>
        </div>
    </div>
</body>

</html>
