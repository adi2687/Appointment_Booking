<!-- client id 453509454133-9ifm6i8m1en3gnb1gct79uh0dd9bpvfu.apps.googleusercontent.com
    client secret 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 -->


<?php
require_once '../vendorgoogle/autoload.php';
require "../dbh.inc.php";
// init configuration
$clientID = '453509454133-9ifm6i8m1en3gnb1gct79uh0dd9bpvfu.apps.googleusercontent.com';
$clientSecret = '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';
$redirectUri = 'http://localhost/MedPulse-main/googlelogin/googlesignup.php';

// create Client Request to access Google API
$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");
function unique()
{
    return rand(100000, 999999);
}
// authenticate code from Google OAuth Flow
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    // Check if there's an error
    if (isset($token['error'])) {
        die('Error fetching access token: ' . htmlspecialchars($token['error']));
    }

    $client->setAccessToken($token['access_token']);
    $google_oauth = new Google_Service_Oauth2($client);

    $google_account_info = $google_oauth->userinfo->get();

    $fname = htmlspecialchars($google_account_info->given_name);
    $lname = htmlspecialchars($google_account_info->family_name);
    $email = htmlspecialchars($google_account_info->email);

    echo $fname . " " . $lname . " email is : " . $email;
    $unique_id = unique();
    $password = "default_password";

    $querycheck = "SELECT * FROM users WHERE email=:email";
    $stmtcheck = $pdo->prepare($querycheck);
    $stmtcheck->bindParam(':email', $email);
    $stmtcheck->execute();
    $resultcheck = $stmtcheck->fetch(PDO::FETCH_ASSOC);
    if ($resultcheck) {
        session_start();
        $_SESSION['unique_id'] = $resultcheck['unique_id'];
        header("Location: ../profile");
        exit;
    }
    $query = "INSERT INTO users (unique_id, fname, lname, email, password) 
              VALUES (:unique_id, :fname, :lname, :email, :password)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":unique_id", $unique_id);
    $stmt->bindParam(":fname", $fname);
    $stmt->bindParam(":lname", $lname);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $password);
    $stmt->execute();
    if ($stmt) {
        session_start();
        $_SESSION['unique_id'] = $unique_id;
        header("Location: ../profile");
    } else {
        echo "couldn't Register , Please Try again";
    }
} else {
    $authUrl = $client->createAuthUrl();
    header("Location: $authUrl");
    exit;

}
?>