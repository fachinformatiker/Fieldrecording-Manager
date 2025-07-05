<?php
require_once 'config.php';
require_once 'functions.php';

$db = getDb();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = isset($_POST['location']) ? sanitizeInput($_POST['location']) : '';
    $notes    = isset($_POST['notes']) ? sanitizeInput($_POST['notes']) : '';
    $id       = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($location) {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE locations SET location = :location, notes = :notes WHERE id = :id");
            $stmt->execute([':location' => $location, ':notes' => $notes, ':id' => $id]);
            $message = "Location updated.";
        } else {
            $stmt = $db->prepare("INSERT INTO locations (location, notes) VALUES (:location, :notes)");
            $stmt->execute([':location' => $location, ':notes' => $notes]);
            $message = "Location added.";
        }
    } else {
        $message = "Please enter a Location.";
    }
}

$stmt = $db->query("SELECT * FROM locations ORDER BY location ASC");
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    
    <div class="grid grid-cols-2">
        <!-- Add New Location -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">📍 Add New Location</h3>
                <p class="card-subtitle">Create a new location for organizing recordings</p>
            </div>
            <div class="card-body">
                <form action="locations.php" method="post">
                    <div class="form-group">
                        <label for="location" class="form-label">Location Name *</label>
                        <input type="text" name="location" id="location" class="form-control" 
                               placeholder="Enter location name (e.g., Central Park, Studio A, Beach)" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" 
                                  placeholder="Optional description, coordinates, or notes about this location"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">➕ Add Location</button>
                </form>
            </div>
        </div>
        
        <!-- All Locations -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">🗺️ All Locations (<?php echo count($locations); ?>)</h3>
                <p class="card-subtitle">Manage existing locations</p>
            </div>
            <div class="card-body">
                <?php if (count($locations) > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>📍 Location</th>
                                    <th>📝 Notes</th>
                                    <th>⚙️ Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($locations as $loc): ?>
                                    <tr>
                                        <td><span class="text-muted">#<?php echo $loc['id']; ?></span></td>
                                        <td>
                                            <span style="background: var(--secondary-color); color: white; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-size: 0.75rem;">
                                                📍 <?php echo htmlspecialchars($loc['location']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($loc['notes'])): ?>
                                                <small><?php echo nl2br(htmlspecialchars($loc['notes'])); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">No notes</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="locations.php" method="post" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                                <input type="hidden" name="id" value="<?php echo $loc['id']; ?>">
                                                <input type="text" name="location" value="<?php echo htmlspecialchars($loc['location']); ?>" 
                                                       style="flex: 1; min-width: 100px; padding: 0.25rem 0.5rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm);" required>
                                                <input type="text" name="notes" value="<?php echo htmlspecialchars($loc['notes']); ?>" 
                                                       placeholder="Notes" style="flex: 1; min-width: 100px; padding: 0.25rem 0.5rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm);">
                                                <button type="submit" class="btn-sm btn-primary">💾 Update</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center mt-4">
                        <h4 class="text-muted">📭 No locations yet</h4>
                        <p class="text-muted">Create your first location using the form on the left!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
