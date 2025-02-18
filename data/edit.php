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

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $notes         = isset($_POST['notes']) ? sanitizeInput($_POST['notes']) : '';
    $tagsInput     = isset($_POST['tags']) ? sanitizeInput($_POST['tags']) : '';
    $locationsInput = isset($_POST['locations']) ? sanitizeInput($_POST['locations']) : '';

    $stmt = $db->prepare("UPDATE recordings SET name = :name, notes = :notes WHERE id = :id");
    $stmt->execute([
        ':name'  => $name,
        ':notes' => $notes,
        ':id'    => $recordingId
    ]);

    $db->prepare("DELETE FROM recording_tags WHERE recording_id = :id")->execute([':id' => $recordingId]);
    $tags = array_filter(array_map('trim', explode(',', $tagsInput)));
    foreach ($tags as $tag) {
        $stmt = $db->prepare("INSERT OR IGNORE INTO tags (tag) VALUES (:tag)");
        $stmt->execute([':tag' => $tag]);
        $stmt = $db->prepare("SELECT id FROM tags WHERE tag = :tag");
        $stmt->execute([':tag' => $tag]);
        $tagId = $stmt->fetchColumn();
        $stmt = $db->prepare("INSERT INTO recording_tags (recording_id, tag_id) VALUES (:recording_id, :tag_id)");
        $stmt->execute([':recording_id' => $recordingId, ':tag_id' => $tagId]);
    }

    $db->prepare("DELETE FROM recording_locations WHERE recording_id = :id")->execute([':id' => $recordingId]);
    $locations = array_filter(array_map('trim', explode(',', $locationsInput)));
    foreach ($locations as $location) {
        $stmt = $db->prepare("INSERT OR IGNORE INTO locations (location) VALUES (:location)");
        $stmt->execute([':location' => $location]);
        $stmt = $db->prepare("SELECT id FROM locations WHERE location = :location");
        $stmt->execute([':location' => $location]);
        $locationId = $stmt->fetchColumn();
        $stmt = $db->prepare("INSERT INTO recording_locations (recording_id, location_id) VALUES (:recording_id, :location_id)");
        $stmt->execute([':recording_id' => $recordingId, ':location_id' => $locationId]);
    }

    $message = "Entries updated!";
    $stmt = $db->prepare("SELECT * FROM recordings WHERE id = :id");
    $stmt->execute([':id' => $recordingId]);
    $recording = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmt = $db->prepare("SELECT t.tag FROM tags t
                      INNER JOIN recording_tags rt ON t.id = rt.tag_id
                      WHERE rt.recording_id = :id");
$stmt->execute([':id' => $recordingId]);
$currentTags = implode(', ', $stmt->fetchAll(PDO::FETCH_COLUMN));

$stmt = $db->prepare("SELECT l.location FROM locations l
                      INNER JOIN recording_locations rl ON l.id = rl.location_id
                      WHERE rl.recording_id = :id");
$stmt->execute([':id' => $recordingId]);
$currentLocations = implode(', ', $stmt->fetchAll(PDO::FETCH_COLUMN));

include 'header.php';
?>

<h2>update Recording</h2>
<?php if ($message): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<form action="edit.php?id=<?php echo $recordingId; ?>" method="post">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($recording['name']); ?>" required><br><br>

    <label for="tags">Tags (comma):</label>
    <input type="text" name="tags" id="tags" value="<?php echo htmlspecialchars($currentTags); ?>"><br><br>

    <label for="locations">Locations (comma):</label>
    <input type="text" name="locations" id="locations" value="<?php echo htmlspecialchars($currentLocations); ?>"><br><br>

    <label for="notes">Notes:</label><br>
    <textarea name="notes" id="notes" rows="5" cols="50"><?php echo htmlspecialchars($recording['notes']); ?></textarea><br><br>

    <input type="submit" value="Update">
</form>

<?php include 'footer.php'; ?>
