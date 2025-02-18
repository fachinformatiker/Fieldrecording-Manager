<?php
require_once 'config.php';
require_once 'functions.php';

$errors = [];

if (!is_dir(DATA_DIR) && !mkdir(DATA_DIR, 0777, true)) {
    $errors[] = "Data directory is not writable.";
} elseif (!is_writable(DATA_DIR)) {
    $errors[] = "Cannot write to data directory.";
}

if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0777, true)) {
    $errors[] = "Upload directory is not writable.";
} elseif (!is_writable(UPLOAD_DIR)) {
    $errors[] = "Cannot write to upload directory.";
}

if (!is_dir(LOG_DIR) && !mkdir(LOG_DIR, 0777, true)) {
    $errors[] = "Log directory is not writable.";
} elseif (!is_writable(LOG_DIR)) {
    $errors[] = "Cannot write to log directory.";
}

try {
    $db = getDb();

    $db->exec("CREATE TABLE IF NOT EXISTS recordings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        file_path TEXT,
        file_size INTEGER,
        md5_hash TEXT,
        notes TEXT,
        deleted INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS tags (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tag TEXT UNIQUE,
        notes TEXT
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS locations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        location TEXT UNIQUE,
        notes TEXT
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS recording_tags (
        recording_id INTEGER,
        tag_id INTEGER,
        FOREIGN KEY (recording_id) REFERENCES recordings(id),
        FOREIGN KEY (tag_id) REFERENCES tags(id)
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS recording_locations (
        recording_id INTEGER,
        location_id INTEGER,
        FOREIGN KEY (recording_id) REFERENCES recordings(id),
        FOREIGN KEY (location_id) REFERENCES locations(id)
    )");

    if (!file_exists(COUNTER_FILE)) {
        file_put_contents(COUNTER_FILE, 0);
    }

    if (!file_exists(LOG_FILE)) {
        file_put_contents(LOG_FILE, 0);
    }

    echo "Installation finished.";
} catch (PDOException $e) {
    logMessage("Installation error: " . $e->getMessage());
    die("Installation error: " . $e->getMessage());
}

if (!empty($errors)) {
    echo "<h3>Installation Failed</h3><ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
    exit;
}

unlink(__FILE__);

echo "<p>Redirecting to the main page...</p>";
echo "<script>setTimeout(() => window.location.href = 'index.php', 1000);</script>";
?>
