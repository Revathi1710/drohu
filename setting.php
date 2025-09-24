<?php
ini_set('display_errors', 1);
include('connection.php');
include('sidebar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2d3748;
            --secondary-color: #718096;
            --background-color: #f7fafc;
            --card-background: #ffffff;
            --border-color: #e2e8f0;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--primary-color);
        }
        .main-container {
            padding: 24px;
        }
        .section-header {
            font-weight: 700;
            margin-bottom: 24px;
        }
        .nav-card {
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: var(--shadow);
            text-decoration: none;
            color: var(--primary-color);
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        .nav-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }
        .nav-card-icon {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 12px;
        }
        .nav-card-title {
            font-weight: 600;
            font-size: 1.25rem;
            margin: 0;
            color: inherit;
        }
    </style>
</head>
<body>
    <div class="main-container">
       
        
        <div class="row g-4">
            <div class="col-md-4 col-sm-6">
                <a href="termspage.php" class="nav-card">
                    <i class="fa-solid fa-file-contract nav-card-icon"></i>
                    <h5 class="nav-card-title">Edit Terms & Conditions</h5>
                </a>
            </div>
            
            <div class="col-md-4 col-sm-6">
                <a href="helpPage.php" class="nav-card">
                    <i class="fa-solid fa-circle-question nav-card-icon"></i>
                    <h5 class="nav-card-title">Edit Help Page</h5>
                </a>
            </div>
            
            <div class="col-md-4 col-sm-6">
                <a href="privacypage.php" class="nav-card">
                    <i class="fa-solid fa-user-shield nav-card-icon"></i>
                    <h5 class="nav-card-title">Edit Privacy Policy</h5>
                </a>
            </div>
        </div>
    </div>
</body>
</html>