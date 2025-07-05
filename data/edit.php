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

<div class="container">
    <?php if ($message): ?>
        <div class="card mb-4" style="border-left: 4px solid var(--success-color);">
            <div class="card-body">
                <p class="mb-0">✅ <?php echo htmlspecialchars($message); ?></p>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title mb-0">✏️ Edit Recording</h3>
            <p class="card-subtitle">Update recording information and metadata</p>
        </div>
        <div class="card-body">
            <form action="edit.php?id=<?php echo $recording['id']; ?>" method="post">
                <div class="grid grid-cols-2">
                    <!-- Basic Information -->
                    <div>
                        <h4 style="margin-bottom: 1rem; color: var(--text-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 0.5rem;">📝 Basic Information</h4>
                        
                        <div class="form-group">
                            <label for="name" class="form-label">Recording Name *</label>
                            <input type="text" name="name" id="name" class="form-control" 
                                   value="<?php echo htmlspecialchars($recording['name']); ?>" 
                                   placeholder="Enter recording name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="6" 
                                      placeholder="Add notes, description, or comments about this recording"><?php echo htmlspecialchars($recording['notes']); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Metadata -->
                    <div>
                        <h4 style="margin-bottom: 1rem; color: var(--text-color); border-bottom: 2px solid var(--secondary-color); padding-bottom: 0.5rem;">🏷️ Metadata</h4>
                        
                        <div class="form-group">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" name="tags" id="tags" class="form-control" 
                                   value="<?php echo htmlspecialchars($currentTags); ?>" 
                                   placeholder="Enter tags separated by commas (e.g., nature, ambient, field)">
                            <small class="text-muted">💡 Separate multiple tags with commas</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="locations" class="form-label">Locations</label>
                            <input type="text" name="locations" id="locations" class="form-control" 
                                   value="<?php echo htmlspecialchars($currentLocations); ?>" 
                                   placeholder="Enter locations separated by commas (e.g., Central Park, Studio A)">
                            <small class="text-muted">💡 Separate multiple locations with commas</small>
                        </div>
                        
                        <!-- Current File Info -->
                        <div style="background: var(--bg-secondary); padding: 1rem; border-radius: var(--radius); margin-top: 1rem;">
                            <h5 style="margin: 0 0 0.5rem 0; color: var(--text-color);">📁 File Information</h5>
                            <p style="margin: 0.25rem 0; font-size: 0.875rem; color: var(--text-muted);"><strong>File:</strong> <?php echo htmlspecialchars($recording['file_path'] ?? 'N/A'); ?></p>
                            <p style="margin: 0.25rem 0; font-size: 0.875rem; color: var(--text-muted);"><strong>Size:</strong> <?php echo number_format(($recording['file_size'] ?? 0) / 1024 / 1024, 2); ?> MB</p>
                            <p style="margin: 0.25rem 0; font-size: 0.875rem; color: var(--text-muted);"><strong>Uploaded:</strong> <?php echo date('M j, Y g:i A', strtotime($recording['created_at'] ?? '1970-01-01')); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color); display: flex; gap: 1rem; justify-content: space-between; align-items: center;">
                    <div>
                        <button type="submit" class="btn btn-primary">💾 Update Recording</button>
                        <a href="detail.php?id=<?php echo $recording['id']; ?>" class="btn btn-secondary">❌ Cancel</a>
                    </div>
                    <div>
                        <a href="detail.php?id=<?php echo $recording['id']; ?>" class="link-button">👁️ View Details</a>
                        <a href="index.php" class="link-button">🏠 Back to List</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Quick Help -->
    <div class="card mt-4">
        <div class="card-header">
            <h4 class="card-title mb-0">💡 Quick Help</h4>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-2">
                <div>
                    <h5>🏷️ Tags</h5>
                    <ul style="margin: 0.5rem 0; padding-left: 1.5rem; color: var(--text-muted); font-size: 0.875rem;">
                        <li>Use descriptive keywords to categorize your recordings</li>
                        <li>Examples: nature, urban, music, interview, ambient</li>
                        <li>Separate multiple tags with commas</li>
                    </ul>
                </div>
                <div>
                    <h5>📍 Locations</h5>
                    <ul style="margin: 0.5rem 0; padding-left: 1.5rem; color: var(--text-muted); font-size: 0.875rem;">
                        <li>Specify where the recording was made</li>
                        <li>Examples: Central Park, Studio A, Beach, Home</li>
                        <li>Can include multiple locations if applicable</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
