<?php

session_start();

include './../components/loggly-logger.php';

// Require user to be logged in
if (!isset($_SESSION['authenticated'])) {
    header('Location: ./../login.php');
    exit();
}

// Base directory for uploaded files (relative to this script)
$baseDir = realpath(__DIR__ . '/uploads');
if ($baseDir === false) {
    die('File storage directory is misconfigured.');
}

if (isset($_GET['file']) && isset($_GET['vault_id'])) {
    $requestedFile = $_GET['file'];
    $vaultId = (int) $_GET['vault_id'];
    $username = $_SESSION['authenticated'];

    // TODO: Enforce vault permissions by checking that $username has access to $vaultId
    // This should mirror the authorization logic used in vault_details.php.

    // Only allow a basename (prevent directory components from user input)
    $requestedName = basename($requestedFile);

    // Build full path under the uploads directory
    $fullPath = $baseDir . DIRECTORY_SEPARATOR . $requestedName;

    // Resolve real path and ensure it stays under $baseDir
    $realPath = realpath($fullPath);
    if ($realPath === false || strpos($realPath, $baseDir) !== 0 || !is_file($realPath)) {
        $logger->warning("Blocked file download attempt: $requestedFile, vault ID: $vaultId, user: $username");
        die('File not found.');
    }

    $logger->info("File download initiated for file: $realPath, in vault ID: $vaultId, user: $username");

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($realPath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($realPath));

    ob_clean();
    flush();

    readfile($realPath);
    exit;
} else {
    die('Invalid file request.');
}

?>