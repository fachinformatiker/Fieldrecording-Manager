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

include 'header.php';
?>

<h2>Recording Details</h2>

<p><strong>ID:</strong> <?php echo $recording['id']; ?></p>
<p><strong>Name:</strong> <?php echo htmlspecialchars($recording['name']); ?></p>
<p><strong>Path:</strong> <?php echo htmlspecialchars($recording['file_path']); ?></p>
<p><strong>Size:</strong> <?php echo $recording['file_size']; ?> Bytes</p>
<p><strong>MD5 Hash:</strong> <?php echo $recording['md5_hash']; ?></p>
<p><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($recording['notes'])); ?></p>
<p><strong>Uploaded on:</strong> <?php echo $recording['created_at']; ?></p>
<p><strong>Tags:</strong> <?php echo implode(', ', $tags); ?></p>
<p><strong>Locations:</strong> <?php echo implode(', ', $locations); ?></p>

<p>
    <a href="<?php echo 'uploads' . $recording['file_path']; ?>" download>Download Audio</a>
</p>

<p>
    <a href="export.php?id=<?php echo $recording['id']; ?>">Download info.txt</a>
</p>

<?php include 'footer.php'; ?>
