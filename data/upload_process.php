<?php
require 'config.php';
require 'functions.php';

include 'header.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$db = getDb();

$uploadDir = __DIR__ . "/uploads/" . date("Y/m/d/");
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    die("File upload error.");
}

$file = $_FILES['file'];
$fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
$filePath = $uploadDir . sprintf("%06X", time()) . "." . $fileExt;

if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    die("File move failed.");
}

$fileHash = md5_file($filePath);
$fileSize = filesize($filePath);

$stmt = $db->prepare("INSERT INTO recordings (name, file_path, file_size, md5_hash, notes) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([
    $_POST['name'],
    $filePath,
    $fileSize,
    $fileHash,
    $_POST['notes'] ?? ''
]);

$recordingId = $db->lastInsertId();

if (!empty($_POST['tags'])) {
    $stmt = $db->prepare("INSERT INTO recording_tags (recording_id, tag_id) VALUES (?, ?)");
    foreach ($_POST['tags'] as $tagId) {
        $stmt->execute([$recordingId, $tagId]);
    }
}

if (!empty($_POST['locations'])) {
    $stmt = $db->prepare("INSERT INTO recording_locations (recording_id, location_id) VALUES (?, ?)");
    foreach ($_POST['locations'] as $locationId) {
        $stmt->execute([$recordingId, $locationId]);
    }
}

echo "Upload successful!";
echo "<script>setTimeout(() => window.location.href = 'index.php', 1000);</script>";
include 'footer.php';
?>
