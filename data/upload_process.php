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

if (!isset($_FILES['file'])) {
    die("No file was uploaded.");
}

$uploadError = $_FILES['file']['error'];
if ($uploadError !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
    ];
    
    $errorMessage = isset($errorMessages[$uploadError]) ? $errorMessages[$uploadError] : 'Unknown upload error.';
    die("File upload error: " . $errorMessage . " (Error code: " . $uploadError . ")");
}

$file = $_FILES['file'];
$fileName = $file['name'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Validate file type
$allowedExtensions = ['mp3', 'wav', 'flac', 'aac', 'ogg', 'm4a', 'wma', 'aiff', 'au'];
if (!in_array($fileExt, $allowedExtensions)) {
    die("Invalid file type. Only audio files are allowed: " . implode(', ', $allowedExtensions));
}

// Validate file size (max 100MB)
$maxFileSize = 100 * 1024 * 1024; // 100MB in bytes
if ($file['size'] > $maxFileSize) {
    die("File too large. Maximum file size is 100MB.");
}

// Generate unique filename
$uniqueId = getNextHexCounter();
$filePath = $uploadDir . $uniqueId . "." . $fileExt;

if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    die("Failed to save uploaded file. Please check directory permissions.");
}

$fileHash = md5_file($filePath);
$fileSize = filesize($filePath);

// Validate required fields
if (empty($_POST['name'])) {
    unlink($filePath); // Clean up uploaded file
    die("Recording name is required.");
}

try {
    $stmt = $db->prepare("INSERT INTO recordings (name, file_path, file_size, md5_hash, notes, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        sanitizeInput($_POST['name']),
        $filePath,
        $fileSize,
        $fileHash,
        sanitizeInput($_POST['notes'] ?? ''),
        date('Y-m-d H:i:s')
    ]);
    
    $recordingId = $db->lastInsertId();
} catch (Exception $e) {
    unlink($filePath); // Clean up uploaded file
    logMessage("Database error during upload: " . $e->getMessage());
    die("Database error occurred. Please try again.");
}

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

// Log successful upload
logMessage("File uploaded successfully: " . $_POST['name'] . " (ID: " . $recordingId . ")");

// Display success message with modern styling
echo '<div class="container" style="margin-top: 2rem;">';
echo '<div class="card" style="border-left: 4px solid var(--success-color);">';
echo '<div class="card-body text-center">';
echo '<h3 style="color: var(--success-color); margin-bottom: 1rem;">🎉 Upload Successful!</h3>';
echo '<p style="margin-bottom: 1rem;">Your recording "<strong>' . htmlspecialchars($_POST['name']) . '</strong>" has been uploaded successfully.</p>';
echo '<div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">';
echo '<a href="detail.php?id=' . $recordingId . '" class="btn btn-primary">👁️ View Recording</a>';
echo '<a href="upload.php" class="btn btn-secondary">➕ Upload Another</a>';
echo '<a href="index.php" class="btn btn-secondary">🏠 Back to List</a>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

// Auto-redirect after 5 seconds
echo '<script>';
echo 'let countdown = 5;';
echo 'const timer = setInterval(() => {';
echo '  countdown--;';
echo '  if (countdown <= 0) {';
echo '    clearInterval(timer);';
echo '    window.location.href = "detail.php?id=' . $recordingId . '";';
echo '  }';
echo '}, 1000);';
echo '</script>';

include 'footer.php';
?>
