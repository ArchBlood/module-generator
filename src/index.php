<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission to generate the module
    require_once 'classes/HumHubModuleGenerator.php';
    $moduleName = $_POST['moduleName'];
    $moduleDescription = $_POST['moduleDescription'];
    $author = $_POST['author'];
    $email = $_POST['email'];
    $homepage = $_POST['homepage'];
    $role = $_POST['role'];

    $generator = new HumHubModuleGenerator($moduleName, $moduleDescription, $author, $email, $homepage, $role);
    $result = $generator->generate();

    echo "<h2>Module '{$moduleName}' Generated!</h2>";
    echo "<p>Download your module package from <a href='{$result['zipPath']}' target='_blank'>{$result['zipPath']}</a></p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HumHub Module Generator</title>
    <link rel="stylesheet" href="static/styles.css">
</head>
<body>
    <div class="container">
        <h1>HumHub Module Generator</h1>
        <form action="" method="POST">
            <div class="form-group">
                <label for="moduleName">Module Name:</label>
                <input type="text" id="moduleName" name="moduleName" required>
            </div>

            <div class="form-group">
                <label for="moduleDescription">Module Description:</label>
                <textarea id="moduleDescription" name="moduleDescription" required></textarea>
            </div>

            <div class="form-group">
                <label for="author">Author Name:</label>
                <input type="text" id="author" name="author" required>
            </div>

            <div class="form-group">
                <label for="email">Author Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="homepage">Author Homepage:</label>
                <input type="url" id="homepage" name="homepage" required>
            </div>

            <div class="form-group">
                <label for="role">Author Role:</label>
                <input type="text" id="role" name="role" required>
            </div>

            <button type="submit">Generate Module</button>
        </form>
    </div>
</body>
</html>
