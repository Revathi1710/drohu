<?php
session_start();

// Set JSON response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if user is authenticated
if (!isset($_SESSION['mobile_number'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Not authenticated'
    ]);
    exit;
}

// Return user information
echo json_encode([
    'success' => true,
    'user' => [
        'mobile' => $_SESSION['mobile_number'],
       
        'session_id' => session_id()
    ]
]);
?>