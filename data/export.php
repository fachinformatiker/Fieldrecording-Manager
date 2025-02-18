<?php
require_once 'config.php';
require_once 'functions.php';

if (!isset($_GET['id'])) {
    die("No ID specified.");
}

$recordingId = (int) $_GET['id'];
$db = getDb();

$stmt = $db->prepare("SELECT * FROM recordings WHERE id = :id AND deleted = 0");
$stmt->execute([':id' => $recordingId]);
$recording = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$recording) {
    die("Recording not found.");
}

$stmt = $db->prepare("SELECT t.tag FROM tags t
                      INNER JOIN recording_tags rt ON t.id = rt.tag_id
                      WHERE rt.recording_id = :id");
$stmt->execute([':id' => $recordingId]);
$tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $db->prepare("SELECT l.location FROM locations l
                      INNER JOIN recording_locations rl ON l.id = rl.location_id
                      WHERE rl.recording_id = :id");
$stmt->execute([':id' => $recordingId]);
$locations = $stmt->fetchAll(PDO::FETCH_COLUMN);

$info  = "Recording Details:\n";
$info .= "ID: " . $recording['id'] . "\n";
$info .= "Name: " . $recording['name'] . "\n";
$info .= "Path: " . $recording['file_path'] . "\n";
$info .= "Size: " . $recording['file_size'] . " Bytes\n";
$info .= "MD5 Hash: " . $recording['md5_hash'] . "\n";
$info .= "Notes: " . $recording['notes'] . "\n";
$info .= "Uploaded on: " . $recording['created_at'] . "\n";
$info .= "Tags: " . implode(', ', $tags) . "\n";
$info .= "Locations: " . implode(', ', $locations) . "\n";
$info .= "\n";
$info .= "\n";
$info .= "Fieldrecording Manager by Patrick Szalewicz - psvisual.de";

$recordingName = $recording['name'];

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="info_' . $recordingId . '_' . $recordingName . '.txt"');
header('Content-Length: ' . strlen($info));
echo $info;
exit;
?>
