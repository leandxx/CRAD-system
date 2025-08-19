<?php
session_start();

// Store these in config.php
$client_id = 'YOUR_GOOGLE_CLIENT_ID';
$redirect_uri = 'https://yoursite.com/gmail-callback.php';

if (isset($_GET['action']) && $_GET['action'] === 'connect') {
    $auth_url = "https://accounts.google.com/o/oauth2/auth?" . http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'scope' => 'https://www.googleapis.com/auth/gmail.send',
        'response_type' => 'code',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ]);
    
    header("Location: " . $auth_url);
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Connect Gmail</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full">
            <h1 class="text-2xl font-bold mb-6 text-center">Connect Your Gmail</h1>
            <p class="mb-6">To send emails to panel members, please authenticate with your Gmail account.</p>
            
            <a href="?action=connect" 
               class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded flex items-center justify-center">
               <i class="fab fa-google mr-2"></i> Connect Gmail
            </a>
            
            <p class="mt-4 text-sm text-gray-600">
                We only request access to send emails. Your credentials are not stored.
            </p>
        </div>
    </div>
</body>
</html>