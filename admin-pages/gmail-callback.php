<?php
session_start();
require '../vendor/autoload.php';

$client->setRedirectUri('http://localhost/CRAD-system/admin-pages/gmail-callback.php');
$client->setClientId('YOUR_CLIENT_ID');
$client->setClientSecret('YOUR_CLIENT_SECRET');
$client->setRedirectUri('https://yoursite.com/gmail-callback.php');
$client->addScope('https://www.googleapis.com/auth/gmail.send');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (!isset($token['error'])) {
        $_SESSION['gmail_authenticated'] = true;
        $_SESSION['gmail_email'] = // Extract email from token;
        $_SESSION['gmail_token'] = $token['access_token'];
        
        // Return to original email sending
        if (isset($_SESSION['email_data'])) {
            header("Location: panel-management.php");
            exit();
        }
    }
}

// Handle errors here
?>

<!-- Simple callback page -->
<!DOCTYPE html>
<html>
<body>
    <script>
        if (window.opener) {
            window.opener.postMessage({gmailAuth: true}, '*');
            window.close();
        } else {
            window.location.href = 'panel-management.php';
        }
    </script>
</body>
</html>