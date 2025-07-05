<?php
if (file_exists(__DIR__ . "/install.php")) {
    header("Location: install.php");
    exit;
}

require_once 'config.php';
require_once 'functions.php';

$db = getDb();

$whereClauses = [];
$params = [];

if (!empty($_GET['name'])) {
    $whereClauses[] = "name LIKE :name";
    $params[':name'] = "%" . sanitizeInput($_GET['name']) . "%";
}

if (!empty($_GET['date'])) {
    $whereClauses[] = "date(created_at) = :date";
    $params[':date'] = sanitizeInput($_GET['date']);
}

$query = "SELECT * FROM recordings WHERE deleted = 0";
if (count($whereClauses) > 0) {
    $query .= " AND " . implode(" AND ", $whereClauses);
}
$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$recordings = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title mb-0">🎵 All Recordings</h2>
            <p class="card-subtitle">Manage and organize your field recordings</p>
        </div>
        
        <div class="card-body">
            <!-- Search and Filter Form -->
            <form method="get" action="index.php" class="mb-4">
                <div class="grid grid-cols-3" style="align-items: end;">
                    <div class="form-group">
                        <label for="name" class="form-label">🔍 Search by Name</label>
                        <input type="text" name="name" id="name" class="form-control" 
                               placeholder="Enter recording name..." 
                               value="<?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date" class="form-label">📅 Filter by Date</label>
                        <input type="date" name="date" id="date" class="form-control" 
                               value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">🔍 Filter Results</button>
                        <?php if (!empty($_GET['name']) || !empty($_GET['date'])): ?>
                            <a href="index.php" class="btn btn-secondary">Clear Filters</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
            
            <!-- Results Summary -->
            <div class="mb-3">
                <p class="text-muted">
                    <?php 
                    $total = count($recordings);
                    echo $total > 0 ? "Showing {$total} recording" . ($total !== 1 ? 's' : '') : 'No recordings found';
                    if (!empty($_GET['name']) || !empty($_GET['date'])) {
                        echo ' (filtered)';
                    }
                    ?>
                </p>
            </div>
            
            <!-- Recordings Table -->
            <?php if (count($recordings) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>📝 Name</th>
                            <th>📅 Uploaded</th>
                            <th>⚙️ Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recordings as $record): ?>
                            <tr>
                                <td><span class="text-muted">#<?php echo $record['id']; ?></span></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($record['name']); ?></strong>
                                </td>
                                <td>
                                    <time datetime="<?php echo $record['created_at']; ?>">
                                        <?php echo date('M j, Y \\a\\t g:i A', strtotime($record['created_at'])); ?>
                                    </time>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="detail.php?id=<?php echo $record['id']; ?>" class="link-button">👁️ View</a>
                                        <a href="edit.php?id=<?php echo $record['id']; ?>" class="link-button">✏️ Edit</a>
                                        <a href="delete.php?id=<?php echo $record['id']; ?>" class="link-button btn-danger">🗑️ Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center mt-5">
                <h3 class="text-muted">📭 No recordings found</h3>
                <p class="text-muted">Start by <a href="upload.php">uploading your first recording</a>!</p>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (count($recordings) > 0): ?>
        <div class="card-footer">
            <div class="text-center">
                <a href="upload.php" class="btn btn-primary">⬆️ Upload New Recording</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
