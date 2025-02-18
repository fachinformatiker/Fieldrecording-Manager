<?php
require_once 'config.php';

function getDb() {
    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("PRAGMA foreign_keys = ON;");
        return $pdo;
    } catch (PDOException $e) {
        logMessage("Database connection error: " . $e->getMessage());
        die("Database connection error.");
    }
}

function logMessage($message) {
    $date = date('Y-m-d H:i:s');
    $entry = "[$date] $message" . PHP_EOL;
    file_put_contents(LOG_FILE, $entry, FILE_APPEND);
}

function getNextHexCounter() {
    if (!file_exists(COUNTER_FILE)) {
        $counter = 0;
    } else {
        $counter = (int) file_get_contents(COUNTER_FILE);
    }
    $counter++;
    file_put_contents(COUNTER_FILE, $counter);
    return str_pad(dechex($counter), 6, '0', STR_PAD_LEFT);
}

function createUploadDirectory($subDir) {
    $path = UPLOAD_DIR . $subDir;
    if (!is_dir($path)) {
        if (!mkdir($path, 0777, true)) {
            logMessage("Failed to create directory: " . $path);
            return false;
        }
    }
    return $path;
}

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>
