<?php

require_once 'HumHubModuleGenerator.php';

session_start();
$message = '';
$error = '';
$zipPath = null; // Initialize $zipPath variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if it's a download request
        if (isset($_POST['download']) && isset($_SESSION['zipPath']) && file_exists($_SESSION['zipPath'])) {
            $zipPath = $_SESSION['zipPath'];
            
            // Send the file to the user
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="'.basename($zipPath).'"');
            header('Content-Length: ' . filesize($zipPath));
            readfile($zipPath);
            
            // Clean up after download
            if (isset($_SESSION['generator'])) {
                $_SESSION['generator']->cleanup();
            }
            
            // Clear the session data
            unset($_SESSION['zipPath']);
            unset($_SESSION['generator']);
            session_destroy();
            
            exit();
        } 
        // If it's a generation request
        else {
            $generator = new HumHubModuleGenerator(
                $_POST['moduleName'] ?? 'custom_module',
                $_POST['translate'] ?? 'translate',
                $_POST['moduleDescription'] ?? 'A custom HumHub module',
                $_POST['author'] ?? 'Developer Name',
                $_POST['email'] ?? 'Email',
                $_POST['homepage'] ?? 'Homepage',
                $_POST['role'] ?? 'Role'
            );
            
            $result = $generator->generate();
            $message = $result['message'];
            
            // Store the generator and zip path in session for the download step
            $_SESSION['generator'] = $generator;
            $_SESSION['zipPath'] = $result['zipPath'];
            $zipPath = $result['zipPath'];
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
        
        // Clean up if there was an error
        if (isset($_SESSION['generator'])) {
            $_SESSION['generator']->cleanup();
            unset($_SESSION['generator']);
        }
        if (isset($_SESSION['zipPath'])) {
            unset($_SESSION['zipPath']);
        }
    }
}

// If there's a stored zipPath in the session, use it
if (!isset($zipPath) && isset($_SESSION['zipPath'])) {
    $zipPath = $_SESSION['zipPath'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HumHub Module Generator</title>
    <link rel="icon" href="/img/32x32.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="static/css/generator.css" rel="stylesheet">
    <script>
        // JavaScript to handle the download action for the button
        function downloadFile(zipPath) {
            var link = document.createElement('a');
            link.href = zipPath;
            link.download = zipPath.split('/').pop();
            link.click();
        }
    </script>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="" class="logo">
                <i class="fa fa-cube"></i> HumHub Module Generator
            </a>
            <nav class="nav-links">
                <a href="">Home</a>
                <a href="">Support</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="generator-form">
            <h1>Generate HumHub Module</h1>
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="form-field">
                    <label for="moduleName">Module Name</label>
                    <input type="text" name="moduleName" id="moduleName" placeholder="Name of the module." required>
                </div>
                <div class="form-field">
                    <label for="translate">Translation</label>
                    <input type="text" name="translate" id="translate" placeholder="Same as the module name." required>
                </div>
                <div class="form-field">
                    <label for="moduleDescription">Module Description</label>
                    <textarea name="moduleDescription" id="moduleDescription" placeholder="Description of the module." required></textarea>
                </div>
                <div class="form-field">
                    <label for="author">Author</label>
                    <input type="text" name="author" id="author" placeholder="Name of creator for the module." required>
                </div>
                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" placeholder="Module creator's email." required>
                </div>
                <div class="form-field">
                    <label for="homepage">Homepage</label>
                    <input type="text" name="homepage" id="homepage" placeholder="Module creator's website." required>
                </div>
                <div class="form-field">
                    <label for="role">Role</label>
                    <input type="text" name="role" id="role" placeholder="Module creator's role. (i.e. Creator, Developer, Owner)"required>
                </div>
                <button type="submit" class="btn-primary">Generate Module</button>
            </form>
            <?php if ($zipPath && file_exists($zipPath)): ?>
                <br>
                <button onclick="downloadFile('<?= htmlspecialchars($zipPath); ?>')" class="btn-primary">Download the Generated Module</button>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-love">
                Made with <i class="fas fa-heart"></i> by Green Meteor
            </div>
        </div>
    </footer>
</body>
</html>
