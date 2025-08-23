<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation Confirmation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
        <div class="text-4xl mb-4">
            <?php if (isset($_SESSION['confirmation_message'])): ?>
                <?php if (strpos($_SESSION['confirmation_message'], 'accepting') !== false): ?>
                    <div class="text-green-500"><i class="fas fa-check-circle"></i></div>
                <?php elseif (strpos($_SESSION['confirmation_message'], 'declined') !== false): ?>
                    <div class="text-red-500"><i class="fas fa-times-circle"></i></div>
                <?php else: ?>
                    <div class="text-blue-500"><i class="fas fa-info-circle"></i></div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-red-500"><i class="fas fa-exclamation-circle"></i></div>
            <?php endif; ?>
        </div>
        
        <h1 class="text-2xl font-bold mb-4">Panel Invitation</h1>
        
        <?php if (isset($_SESSION['confirmation_message'])): ?>
            <p class="text-gray-700 mb-6"><?php echo $_SESSION['confirmation_message']; ?></p>
            <?php unset($_SESSION['confirmation_message']); ?>
        <?php else: ?>
            <p class="text-gray-700 mb-6">An error occurred processing your response.</p>
        <?php endif; ?>
        
        <a href="#" 
   onclick="window.close(); return false;" 
   class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
   <i class="fas fa-home mr-2"></i> Return to Home
</a>
    </div>
</body>
</html>