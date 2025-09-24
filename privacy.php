<?php
include("connection.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

function getAbout2(){
    global $con;
    $query = "SELECT * FROM privacy WHERE id='1' LIMIT 1";
    $query_run = mysqli_query($con, $query);

    if ($query_run && mysqli_num_rows($query_run) > 0) {
        return mysqli_fetch_assoc($query_run);
    } else {
        // We'll handle this more gracefully in the new UI
        return null;
    }
}

$getAbout2 = getAbout2();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-blue': '#1e40af', // A slightly darker, more professional blue
                        'dark-gray': '#1f2937',    // A deep gray for text
                        'light-gray': '#f3f4f6',   // A subtle background gray
                        'border-color': '#e5e7eb', // A light gray for borders
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        :root{ --z-primary:#7a1fa2; --z-primary-2:#b42acb; --bg:#f6f7fb; --card:#fff; --border:#eef2f7; --muted:#6b7280; }
    body{ margin:0; background:var(--bg); font-family:Inter,system-ui,Segoe UI,Roboto,Arial,sans-serif; }

    /* Header */
    .z-header{ position:sticky; top:0; z-index:10; color:#fff;
        background:linear-gradient(135deg,#1a9cfa 0%,#0d6efd 100%);
        border-radius:0 0 18px 18px; box-shadow:0 6px 18px rgba(0,0,0,.15); }
    .z-head{ display:flex; align-items:center; justify-content:space-between; padding:16px; }
    .z-title{ font-weight:800; }
    </style>
</head>
<body>

<header class="z-header">
    <div class="z-head">
        <div class="z-title">Privacy Policy</div>
        <a href="profile.php" style="color:#fff;text-decoration:none"><i class="fa-solid fa-xmark"></i></a>
    </div>
</header>


    <main class="container mx-auto px-4 max-w-4xl py-8">
        <div class="bg-white p-8 sm:p-12 rounded-lg shadow-lg border border-border-color">
            <?php if ($getAbout2 && !empty($getAbout2['content'])): ?>
                <div class="prose max-w-none">
                    <?= $getAbout2['content']?>
                </div>
            <?php else: ?>
                <div class="text-center text-gray-500 py-12">
                    <i class="fa-regular fa-file-lines text-5xl mb-4"></i>
                    <p class="text-lg">Terms and Conditions content not found.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>