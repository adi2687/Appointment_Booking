<?php
require "../dbh.inc.php";
require '../vendor/autoload.php'; // Include PHPMailer autoloader
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    // Fetch the email from POST data
    $found=false;
    $email = $_POST['email'];
    $query = "SELECT * FROM users WHERE email=:email";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $result = $stmt->fetch();
    if ($result) {
        
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'adityakuraniyt@gmail.com'; // Fetch from environment
            $mail->Password = 'zcbb cull rbut dbke'; // Fetch from environment
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('adityakuraniyt@gmail.com', 'MedPulse'); // Sender email
            $mail->addAddress($email); // Receiver email

            $mail->isHTML(true);
            $mail->Subject = 'Use this OTP to login to MedPulse';

            // Generate OTP
            function getOTP()
            {
                return rand(100000, 999999); // Generate a 6-digit OTP
            }

            $otp = getOTP();

            $mail->Body = "Your OTP for MedPulse login is: <b>$otp</b>";

            // Save OTP to session
            $_SESSION['email'] = $email;
            $_SESSION['otp'] = $otp;

            if ($mail->send()) {
                echo "OTP sent successfully to $email";
                header("Location: verify_otp.php"); // Redirect to OTP form for verification
            } else {
                echo "Failed to send OTP. Error: " . $mail->ErrorInfo;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }

        $found=true;
    } 
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
<script>
     let found = <?php echo json_encode($found); ?>;

if (!found) {
    alert("No account found with the given email. Try logging in with a different account.");
    window.location.href = "../login_otp"; // Redirect user to the login page
}
</script>
</body>

</html>