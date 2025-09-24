<?php
include("connection.php");
ini_set('display_errors', 0);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: allProduct.php');
    exit;
}

$product_id = $_GET['id'];
$status_message = '';

// Handle form submission for updating the product
if (isset($_POST['update'])) {
    $product_name = mysqli_real_escape_string($con, $_POST['product_name']);
    $capacity = mysqli_real_escape_string($con, $_POST['capacity']);
    $shelf_life = mysqli_real_escape_string($con, $_POST['shelf_life']);
    $purification = mysqli_real_escape_string($con, $_POST['purification']);
    $original_price = mysqli_real_escape_string($con, $_POST['original_price']);
    $selling_price = mysqli_real_escape_string($con, $_POST['selling_price']);
    $product_image = mysqli_real_escape_string($con, $_POST['current_image']);

    // Handle the new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid() . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Delete the old image file if it exists and is not a default/placeholder image
            if (!empty($_POST['current_image']) && file_exists($_POST['current_image'])) {
                unlink($_POST['current_image']);
            }
            $product_image = $target_file;
        } else {
            $status_message = "Sorry, there was an error uploading your file.";
        }
    }

    // Corrected SQL query to update product data and image path
    $sql = "UPDATE product SET product_name='$product_name', capacity='$capacity', shelf_life='$shelf_life', purification='$purification', original_price='$original_price', selling_price='$selling_price', product_image='$product_image' WHERE id=$product_id";
    
    if (mysqli_query($con, $sql)) {
        header("Location: allProduct.php");
        exit;
    } else {
        $status_message = "Error: " . mysqli_error($con);
    }
}

// Fetch the existing product data
$query = "SELECT * FROM product WHERE id = $product_id";
$result = mysqli_query($con, $query);
if (mysqli_num_rows($result) === 0) {
    header('Location: allProduct.php');
    exit;
}
$product = mysqli_fetch_assoc($result);

include("sidebar.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
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

        .main-container {
            min-height: 100vh;
            padding: 1.5rem;
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
        
        .current-image-container {
            margin-top: 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #fafbfc;
        }
        .current-image-container img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: var(--radius-sm);
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
                    <i class="fas fa-box"></i>
                    Edit Product
                </h1>
                <p class="page-subtitle">Update product information in your inventory</p>
            </div>
            
            <?php if (!empty($status_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $status_message ?>
                </div>
            <?php endif; ?>

            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="current_image" value="<?= htmlspecialchars($product['product_image']) ?>">
                <div class="form-card">
                    <div class="form-card-header">
                        <h5>
                            <i class="fas fa-info-circle"></i>
                            Product Details
                        </h5>
                    </div>
                    <div class="form-card-body">
                        <div class="form-section">
                            <h6 class="section-title">
                                <i class="fas fa-cube"></i>
                                Basic Information
                            </h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-box-open"></i>
                                        Product Name <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="product_name"
                                           placeholder="Enter product name" value="<?= htmlspecialchars($product['product_name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-flask"></i>
                                        Capacity<span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="capacity"
                                           placeholder="Enter capacity" value="<?= htmlspecialchars($product['capacity']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-tags"></i>
                                        Original Price<span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="original_price"
                                           placeholder="Enter Original Price" value="<?= htmlspecialchars($product['original_price']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-dollar-sign"></i>
                                        Selling Price<span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="selling_price"
                                           placeholder="Enter Selling Price" value="<?= htmlspecialchars($product['selling_price']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-clock"></i>
                                        Shelf Life <span class="required-indicator">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="shelf_life"
                                           placeholder="Enter shelf life" value="<?= htmlspecialchars($product['shelf_life']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-filter"></i>
                                        Water Purification
                                    </label>
                                    <input type="text" class="form-control" name="purification"
                                           placeholder="Enter water purification method" value="<?= htmlspecialchars($product['purification']) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-image"></i>
                                        Image
                                    </label>
                                    <input type="file" class="form-control" name="image">
                                    <?php if (!empty($product['product_image'])): ?>
                                        <div class="current-image-container">
                                            <img src="<?= htmlspecialchars($product['product_image']) ?>" alt="Current Product Image">
                                            <span>Current Image</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="submit-section">
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="submit-btn" name="update">
                                    <i class="fas fa-save"></i>
                                    Update Product
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