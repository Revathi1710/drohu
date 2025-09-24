<?php
include('sidebar.php');
include('connection.php');
ini_set('display_errors', 1);

// Fetch data from database (prepared statement)
$stmt = $con->prepare("SELECT content FROM help WHERE id = ? LIMIT 1");
$id = 1;
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
} else {
    // Gracefully handle "Guidelines page not found" on the front end
    $data['content'] = 'Page content not found. Please add new content.';
}
$stmt->close();

// Update logic
if (isset($_POST['updatepage'])) {
    $aboutContent = $_POST['aboutContent'] ?? '';

    $stmt = $con->prepare("UPDATE help SET content = ? WHERE id = ?");
    $stmt->bind_param("si", $aboutContent, $id);

    if ($stmt->execute()) {
        echo '<script>alert("Page Updated Successfully");</script>';
        echo '<script>window.location.href="helpPage.php";</script>';
    } else {
        echo '<script>alert("Something went wrong: ' . $stmt->error . '");</script>';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Help Page</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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
        .header-card {
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-card h5 {
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }
        .content-card {
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 24px;
            margin-top: 24px;
        }
        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        .form-control {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
        }
        .ck-editor__editable[role="textbox"] {
            min-height: 400px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background-color: #4a5568;
            border-color: #4a5568;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header-card">
            <h5 class="fs-5">Update Help Page</h5>
        </div>

        <div class="content-card">
            <form action="" method="post">
                <div class="mb-3">
                    <label for="aboutContent" class="form-label">Content</label>
                    <textarea name="aboutContent" id="aboutContent" class="form-control" required>
                        <?= isset($data['content']) ? htmlspecialchars($data['content']) : '' ?>
                    </textarea>
                </div>

                <div class="d-grid d-md-block">
                    <button type="submit" name="updatepage" class="btn btn-primary">Update Page</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#aboutContent'))
            .catch(error => {
                console.error(error);
            });
    </script>
</body>
</html>