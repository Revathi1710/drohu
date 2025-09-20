<?php
include("connection.php");
ini_set('display_errors', 0);

if (isset($_POST['add'])) {
    $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
    $capacity = mysqli_real_escape_string($con, $_POST['capacity']);
    $shelf_life = mysqli_real_escape_string($con, $_POST['shelf_life']);
    $purification = mysqli_real_escape_string($con, $_POST['purification']);
    $product_image = '';

    // Handle the image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Define the target directory for uploads
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create directory if it doesn't exist
        }

        // Create a unique filename to prevent overwrites
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid() . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        // Move the uploaded file from the temporary directory to the target directory
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $product_image = $target_file; // Store the relative path
        } else {
            // Error handling for file move
            echo "Sorry, there was an error uploading your file.";
        }
    }

    // Corrected SQL query to insert product data and image path
    $sql = "INSERT INTO product (product_name, capacity, shelf_life, purification, product_image) VALUES ('$product_name', '$capacity', '$shelf_life', '$purification', '$product_image')";
    mysqli_query($con, $sql);

    // Redirect to avoid duplicate form submission
    if (mysqli_affected_rows($con) > 0) {
        header("Location: allProduct.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($con);
    }
}

include("sidebar.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0D9488;
            --primary-hover: #0F766E;
            --secondary-color: #64748b;
            --success-color: #10B981;
            --danger-color: #EF4444;
            --warning-color: #FBBF24;
            --light-bg: #F9FAFB;
            --white: #ffffff;
            --border-color: #E5E7EB;
            --text-primary: #1F2937;
            --text-secondary: #6B7280;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
        }

        * {
            box-sizing: border-box;
        }

        body {
             background: var(--light-bg);
             font-family: 'Inter', sans-serif;
        }

        .main-container {
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        .form-container {
             
             margin: 0 auto;
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
            font-weight: 700;
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
            font-size: 1rem;
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
            background: linear-gradient(135deg, #2DD4BF, #0D9488);
            color: white;
            padding: 1.5rem 2rem;
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
            border-bottom: 2px solid var(--border-color);
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
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .form-label i {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background: var(--white);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgb(13 148 136 / 0.1);
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
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 0.875rem 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-width: 200px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgb(13 148 136 / 0.3);
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
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-box"></i>
                    Add New Product
                </h1>
                <p class="page-subtitle">Add a new product to your inventory</p>
            </div>

            <!-- Main Form -->
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-card">
                    <div class="form-card-header">
                        <h5>
                            <i class="fas fa-info-circle"></i>
                            Product Details
                        </h5>
                    </div>
                    <div class="form-card-body">
                        <!-- Product Details Section -->
                        <div class="form-section">
                            <h6 class="section-title">
                                <i class="fas fa-cube"></i>
                                Basic Information
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">
                                        <i class="fas fa-box-open"></i>
                                        Product Name <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="product_name"
                                           placeholder="Enter product name" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">
                                        <i class="fas fa-flask"></i>
                                        Capacity<span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="capacity"
                                           placeholder="Enter capacity" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">
                                        <i class="fas fa-clock"></i>
                                        Shelf Life <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="shelf_life"
                                           placeholder="Enter shelf life" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">
                                        <i class="fas fa-filter"></i>
                                        Water Purification
                                    </label>
                                    <input type="text" class="form-control" name="purification"
                                           placeholder="Enter water purification method">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">
                                        <i class="fas fa-image"></i>
                                        Image <span class="required-indicator">*</span>
                                    </label>
                                    <input type="file" class="form-control" name="image"
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Section -->
                        <div class="submit-section">
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="submit-btn" name="add">
                                    <i class="fas fa-save"></i>
                                    Save
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