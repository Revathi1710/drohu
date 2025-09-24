<?php
session_start();
require_once __DIR__ . '/connection.php';
include __DIR__ . '/sidebar.php';

$successMessage = '';
$errorMessage = '';

// Data from form submission
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$number = $_POST['number'] ?? '';
$email = $_POST['email'] ?? '';


// --- VALIDATION FUNCTIONS ---
function is_valid_name(string $name): bool {
    return $name !== '' && mb_strlen($name) <= 80;
}

function is_valid_mobile(string $mobile): bool {
    return preg_match('/^\d{10}$/', $mobile) === 1;
}

function is_valid_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false && mb_strlen($email) <= 120;
}

// --- FORM SUBMISSION LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Trim all inputs to remove whitespace
    $first_name = trim($first_name);
    $last_name = trim($last_name);
    $number = trim($number);
    $email = trim($email);

    // Perform validation checks
    if (!is_valid_name($first_name) || !is_valid_name($last_name)) {
        $errorMessage = 'Please enter a valid first and last name.';
    } elseif (!is_valid_mobile($number)) {
        $errorMessage = 'Please enter a valid 10-digit mobile number.';
    } elseif (!is_valid_email($email)) {
        $errorMessage = 'Please enter a valid email address.';
    } else {
        // Prepare and execute duplicate checks
        $dupStmt = $con->prepare('SELECT email, number FROM user WHERE email = ? OR number = ? LIMIT 1');
        if ($dupStmt) {
            $dupStmt->bind_param('ss', $email, $number);
            $dupStmt->execute();
            $dupResult = $dupStmt->get_result();
            $dup = $dupResult->fetch_assoc();
            $dupStmt->close();

            if ($dup) {
                if (isset($dup['email']) && $dup['email'] === $email) {
                    $errorMessage = 'Email already in use.';
                } elseif (isset($dup['number']) && $dup['number'] === $number) {
                    $errorMessage = 'Mobile number already in use.';
                }
            } else {
                // Prepare and execute the insert statement
                $sql = 'INSERT INTO user (first_name, last_name, number, email) VALUES (?, ?, ?, ?)';
                $stmt = $con->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param('ssss', $first_name, $last_name, $number, $email);

                    if ($stmt->execute()) {
                        $successMessage = 'Customer added successfully.';
                        // Clear form values on success
                        $first_name = $last_name = $number = $email = '';
                    } else {
                        $errorMessage = 'Insert failed. Please try again.';
                    }
                    $stmt->close();
                } else {
                    $errorMessage = 'Failed to prepare insert statement.';
                }
            }
        } else {
            $errorMessage = 'Failed to prepare duplicate check.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4A90E2;
            --primary-hover: #3A7BC8;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --light-bg: #F0F4F8;
            --white: #ffffff;
            --border-color: #E0E6ED;
            --text-primary: #212529;
            --text-secondary: #6C757D;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 2px 4px 0 rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 4px 8px 0 rgba(0, 0, 0, 0.12);
            --radius-sm: 0.25rem;
            --radius-md: 0.35rem;
            --radius-lg: 0.5rem;
        }

        * {
            box-sizing: border-box;
        }

        body {
             background: var(--light-bg);
             font-family: 'Inter', sans-serif;
             font-size: 0.9375rem;
        }

       

        .page-header {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }
        .page-title {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.5rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-title i {
            color: var(--primary-color);
            font-size: 1.75rem;
        }

        .page-subtitle {
            color: var(--text-secondary);
            margin: 0.5rem 0 0 0;
            font-size: 0.9rem;
            font-weight: 400;
        }

        .form-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .form-card-header {
            background: var(--white);
            color: var(--text-primary);
            padding: 1.25rem 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .form-card-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.125rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-card-body {
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.125rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--primary-color);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            font-size: 0.825rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .form-label i {
            color: var(--text-secondary);
            font-size: 0.8rem;
        }

        .form-control {
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 0.65rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: var(--white);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
            outline: none;
        }

        .form-control::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }

        .submit-section {
            background: #f8fafc;
            margin: 2rem -2rem -2rem;
            padding: 2rem;
            border-top: 1px solid var(--border-color);
        }

        .submit-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            padding: 0.75rem 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-width: 180px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .submit-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }
        
        .required-indicator {
            color: var(--danger-color);
            font-weight: 700;
        }
        
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            .page-header {
                padding: 1.5rem;
            }
            .form-card-body {
                padding: 1.5rem;
            }
            .page-title {
                font-size: 1.5rem;
            }
            .submit-section {
                margin: 1.5rem -1.5rem -1.5rem;
                padding: 1.5rem;
            }
        }
        
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="form-container fade-in">
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-user-plus"></i>
                    Add Customer
                </h1>
                <p class="page-subtitle">Create a new customer account</p>
            </div>
            
            <?php if ($successMessage): ?>
                <div class="alert alert-success py-2" role="alert"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger py-2" role="alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="post" action="" novalidate>
                <div class="form-card">
                    <div class="form-card-header">
                        <h5>
                            <i class="fas fa-info-circle"></i>
                            Customer Details
                        </h5>
                    </div>
                    <div class="form-card-body">
                        <div class="form-section">
                            <h6 class="section-title">
                                <i class="fas fa-user"></i>
                                Basic Information
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name <span class="required-indicator">*</span></label>
                                    <input type="text" name="first_name" class="form-control" placeholder="e.g., Rohan" value="<?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name <span class="required-indicator">*</span></label>
                                    <input type="text" name="last_name" class="form-control" placeholder="e.g., Kumar" value="<?php echo htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mobile Number <span class="required-indicator">*</span></label>
                                    <input type="text" name="number" class="form-control" placeholder="10-digit number" inputmode="numeric" pattern="\d{10}" maxlength="10" value="<?php echo htmlspecialchars($number, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="required-indicator">*</span></label>
                                    <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="submit-section">
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="submit-btn">
                                    <i class="fas fa-save"></i>
                                    Save Customer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>