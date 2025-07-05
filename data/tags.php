<?php
require_once 'config.php';
require_once 'functions.php';

$db = getDb();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tag   = isset($_POST['tag']) ? sanitizeInput($_POST['tag']) : '';
    $notes = isset($_POST['notes']) ? sanitizeInput($_POST['notes']) : '';
    $id    = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($tag) {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE tags SET tag = :tag, notes = :notes WHERE id = :id");
            $stmt->execute([':tag' => $tag, ':notes' => $notes, ':id' => $id]);
            $message = "Tag updated.";
        } else {
            $stmt = $db->prepare("INSERT INTO tags (tag, notes) VALUES (:tag, :notes)");
            $stmt->execute([':tag' => $tag, ':notes' => $notes]);
            $message = "Tag added.";
        }
    } else {
        $message = "Please enter a Tag.";
    }
}

$stmt = $db->query("SELECT * FROM tags ORDER BY tag ASC");
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <!-- Add New Tag -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">🏷️ Add New Tag</h3>
                <p class="card-subtitle">Create a new tag for organizing recordings</p>
            </div>
            <div class="card-body">
                <form action="tags.php" method="post">
                    <div class="form-group">
                        <label for="tag" class="form-label">Tag Name *</label>
                        <input type="text" name="tag" id="tag" class="form-control" 
                               placeholder="Enter tag name (e.g., Nature, Urban, Music)" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" 
                                  placeholder="Optional description or notes about this tag"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">➕ Add Tag</button>
                </form>
            </div>
        </div>
        
        <!-- All Tags -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">📋 All Tags (<?php echo count($tags); ?>)</h3>
                <p class="card-subtitle">Manage existing tags</p>
            </div>
            <div class="card-body">
                <?php if (count($tags) > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>🏷️ Tag</th>
                                    <th>📝 Notes</th>
                                    <th>⚙️ Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tags as $tag): ?>
                                    <tr>
                                        <td><span class="text-muted">#<?php echo $tag['id']; ?></span></td>
                                        <td>
                                            <span style="background: var(--primary-color); color: white; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-size: 0.75rem;">
                                                🏷️ <?php echo htmlspecialchars($tag['tag']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($tag['notes'])): ?>
                                                <small><?php echo nl2br(htmlspecialchars($tag['notes'])); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">No notes</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="tags.php" method="post" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                                                <input type="hidden" name="id" value="<?php echo $tag['id']; ?>">
                                                <input type="text" name="tag" value="<?php echo htmlspecialchars($tag['tag']); ?>" 
                                                       style="flex: 1; min-width: 100px; padding: 0.25rem 0.5rem; border: 1px solid var(--border-color); border-radius: var(--radius-sm);" required>
                                                <input type="text" name="notes" value="<?php echo htmlspecialchars($tag['notes']); ?>" 
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
                        <h4 class="text-muted">📭 No tags yet</h4>
                        <p class="text-muted">Create your first tag using the form on the left!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
