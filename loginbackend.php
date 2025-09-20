<?php
// ---------------------------
// Long-lived session settings
// ---------------------------
$lifetime = 315360000; // 10 years in seconds
session_set_cookie_params([
    'lifetime' => $lifetime,
    'path' => '/',
    'secure' => false, // set true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
ini_set('session.gc_maxlifetime', $lifetime);
session_start();

// Set JSON response
header('Content-Type: application/json');

// ---------------------------
// Include database connection
// ---------------------------
include('connection.php');

// ---------------------------
// Decode incoming JSON
// ---------------------------
$data = json_decode(file_get_contents('php://input'), true);
$mobile_number = $data['mobile_number'] ?? null;
$otp_from_user = $data['otp'] ?? null;

// ---------------------------
// OTP (for demo purposes)
// ---------------------------
$correct_otp = '1234'; // replace with dynamic OTP in production

// ---------------------------
// Case 1: OTP verification
// ---------------------------
if ($otp_from_user) {
    if ($otp_from_user === $correct_otp) {
        // Use prepared statement to fetch user
        $stmt = $con->prepare("SELECT id, mobile_number FROM users WHERE mobile_number = ?");
        $stmt->bind_param("s", $mobile_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $stmt->close();

        if ($user_data) {
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['mobile_number'] = $user_data['mobile_number'];
            echo json_encode(['success' => true, 'message' => 'OTP verified.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found after OTP verification.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP.']);
    }
    $con->close();
    exit;
}

// ---------------------------
// Case 2: Mobile number login/registration
// ---------------------------
if ($mobile_number) {
    // Check if user exists
    $stmt = $con->prepare("SELECT id, mobile_number FROM users WHERE mobile_number = ?");
    $stmt->bind_param("s", $mobile_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Existing user: login
        $user_data = $result->fetch_assoc();
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['mobile_number'] = $user_data['mobile_number'];
        echo json_encode(['success' => true, 'message' => 'Login successful.', 'otp_required' => false]);
    } else {
        // New user: insert
        $stmt_insert = $con->prepare("INSERT INTO users (mobile_number) VALUES (?)");
        $stmt_insert->bind_param("s", $mobile_number);
        if ($stmt_insert->execute()) {
            $new_user_id = $stmt_insert->insert_id;
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['mobile_number'] = $mobile_number;

            // Here you would generate/send real OTP
            echo json_encode(['success' => true, 'message' => 'New user registered. OTP sent.', 'otp_required' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error registering user: ' . $stmt_insert->error]);
        }
        $stmt_insert->close();
    }

    $stmt->close();
    $con->close();
    exit;
}

// ---------------------------
// Error: no mobile number provided
// ---------------------------
echo json_encode(['success' => false, 'message' => 'Mobile number is required.']);
$con->close();
exit;
?>
