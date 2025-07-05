<?php
/**
 * Audio file server with HTTP Range Request support
 * Enables seeking in audio players by supporting partial content requests
 */

require_once 'config.php';
require_once 'functions.php';

// Get the requested file path
$file = $_GET['file'] ?? '';

if (empty($file)) {
    http_response_code(400);
    exit('No file specified');
}

// Security: Ensure the file is within the uploads directory
$uploadsDir = realpath(UPLOAD_DIR);

// Remove 'uploads/' prefix if present since UPLOAD_DIR already points to uploads
$cleanFile = $file;
if (strpos($file, 'uploads/') === 0) {
    $cleanFile = substr($file, 8); // Remove 'uploads/' prefix
}

$requestedFile = realpath(UPLOAD_DIR . '/' . $cleanFile);

if (!$requestedFile || strpos($requestedFile, $uploadsDir) !== 0) {
    http_response_code(403);
    exit('Access denied');
}

if (!file_exists($requestedFile)) {
    http_response_code(404);
    exit('File not found');
}

// Get file info
$fileSize = filesize($requestedFile);
$fileName = basename($requestedFile);
$fileExt = strtolower(pathinfo($requestedFile, PATHINFO_EXTENSION));

// Determine MIME type
$mimeTypes = [
    'mp3' => 'audio/mpeg',
    'wav' => 'audio/wav',
    'flac' => 'audio/flac',
    'ogg' => 'audio/ogg',
    'm4a' => 'audio/mp4',
    'aac' => 'audio/aac',
    'wma' => 'audio/x-ms-wma',
    'aiff' => 'audio/aiff',
    'au' => 'audio/basic'
];
$mimeType = $mimeTypes[$fileExt] ?? 'application/octet-stream';

// Handle range requests
$range = $_SERVER['HTTP_RANGE'] ?? '';

if (!empty($range)) {
    // Parse range header
    if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
        $start = intval($matches[1]);
        $end = !empty($matches[2]) ? intval($matches[2]) : $fileSize - 1;
        
        // Validate range
        if ($start >= $fileSize || $end >= $fileSize || $start > $end) {
            http_response_code(416); // Range Not Satisfiable
            header("Content-Range: bytes */$fileSize");
            exit();
        }
        
        $length = $end - $start + 1;
        
        // Send partial content headers
        http_response_code(206); // Partial Content
        header("Content-Range: bytes $start-$end/$fileSize");
        header("Content-Length: $length");
        header("Accept-Ranges: bytes");
        header("Content-Type: $mimeType");
        header("Cache-Control: public, max-age=3600");
        
        // Send partial file content
        $file = fopen($requestedFile, 'rb');
        fseek($file, $start);
        
        $bufferSize = 8192;
        $bytesRemaining = $length;
        
        while ($bytesRemaining > 0 && !feof($file)) {
            $bytesToRead = min($bufferSize, $bytesRemaining);
            echo fread($file, $bytesToRead);
            $bytesRemaining -= $bytesToRead;
            
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        }
        
        fclose($file);
    } else {
        // Invalid range format
        http_response_code(400);
        exit('Invalid range format');
    }
} else {
    // Send full file
    header("Content-Type: $mimeType");
    header("Content-Length: $fileSize");
    header("Accept-Ranges: bytes");
    header("Cache-Control: public, max-age=3600");
    header("Content-Disposition: inline; filename=\"$fileName\"");
    
    readfile($requestedFile);
}
?>