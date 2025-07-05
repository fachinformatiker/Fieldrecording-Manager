<?php
require 'config.php';
require 'functions.php';

$db = getDb();

$tags = $db->query("SELECT id, tag FROM tags ORDER BY tag")->fetchAll(PDO::FETCH_ASSOC);

$locations = $db->query("SELECT id, location FROM locations ORDER BY location")->fetchAll(PDO::FETCH_ASSOC);
include 'header.php' ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title mb-0">⬆️ Upload Field Recording</h2>
            <p class="card-subtitle">Add a new recording to your collection</p>
        </div>
        
        <div class="card-body">
            <form action="upload_process.php" method="post" enctype="multipart/form-data" id="uploadForm">
                <!-- Maximum file size: 100MB -->
                <input type="hidden" name="MAX_FILE_SIZE" value="104857600">
                <div class="grid grid-cols-2">
                    <div class="form-group">
                        <label for="name" class="form-label">📝 Recording Name *</label>
                        <input type="text" name="name" id="name" class="form-control" 
                               placeholder="Enter a descriptive name for your recording" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="file" class="form-label">🎵 Audio File *</label>
                        <input type="file" name="file" id="file" class="form-control" 
                               accept="audio/*,.wav,.mp3,.flac,.aac,.ogg,.m4a" required>
                        <small class="text-muted">Supported formats: WAV, MP3, FLAC, AAC, OGG, M4A</small>
                    </div>
                </div>
                
                <div class="grid grid-cols-2">
                    <div class="form-group">
                        <label for="tags" class="form-label">🏷️ Tags</label>
                        <select name="tags[]" id="tags" class="form-control" multiple>
                            <?php foreach ($tags as $tag): ?>
                                <option value="<?= htmlspecialchars($tag['id']) ?>">
                                    <?= htmlspecialchars($tag['tag']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple tags</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="locations" class="form-label">📍 Locations</label>
                        <select name="locations[]" id="locations" class="form-control" multiple>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?= htmlspecialchars($location['id']) ?>">
                                    <?= htmlspecialchars($location['location']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple locations</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes" class="form-label">📋 Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="4" 
                              placeholder="Add any additional notes about this recording (equipment used, weather conditions, etc.)"></textarea>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="btn btn-primary" id="uploadBtn">⬆️ Upload Recording</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
                
                <!-- Upload Progress -->
                <div id="uploadProgress" style="display: none; margin-top: 1rem;">
                    <div style="background: var(--bg-secondary); padding: 1rem; border-radius: var(--radius); text-center;">
                        <p>🔄 Uploading your recording...</p>
                        <div style="background: var(--border-color); height: 8px; border-radius: 4px; overflow: hidden;">
                            <div id="progressBar" style="background: var(--primary-color); height: 100%; width: 0%; transition: width 0.3s ease;"></div>
                        </div>
                        <small class="text-muted">Please wait while your file is being uploaded and processed.</small>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="card-footer">
            <div class="text-center">
                <small class="text-muted">
                    Need to add new tags or locations? Visit 
                    <a href="tags.php">Tags</a> or <a href="locations.php">Locations</a> pages.
                </small>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('uploadForm');
    const fileInput = document.getElementById('file');
    const uploadBtn = document.getElementById('uploadBtn');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    
    // File validation
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        
        // Check file size (100MB limit)
        const maxSize = 100 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File too large! Maximum file size is 100MB.');
            this.value = '';
            return;
        }
        
        // Check file type
        const allowedTypes = ['audio/mpeg', 'audio/wav', 'audio/flac', 'audio/aac', 'audio/ogg', 'audio/mp4', 'audio/x-m4a'];
        const allowedExtensions = ['.mp3', '.wav', '.flac', '.aac', '.ogg', '.m4a', '.wma', '.aiff', '.au'];
        
        const isValidType = allowedTypes.includes(file.type) || 
                           allowedExtensions.some(ext => file.name.toLowerCase().endsWith(ext));
        
        if (!isValidType) {
            alert('Invalid file type! Please select an audio file.');
            this.value = '';
            return;
        }
        
        // Show file info
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        console.log(`Selected file: ${file.name} (${fileSize} MB)`);
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        const file = fileInput.files[0];
        const name = document.getElementById('name').value.trim();
        
        if (!file) {
            alert('Please select an audio file to upload.');
            e.preventDefault();
            return;
        }
        
        if (!name) {
            alert('Please enter a recording name.');
            e.preventDefault();
            return;
        }
        
        // Show upload progress
        uploadBtn.disabled = true;
        uploadBtn.textContent = '🔄 Uploading...';
        uploadProgress.style.display = 'block';
        
        // Simulate progress (since we can't track real progress with standard form submission)
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
        }, 500);
        
        // Clean up interval after form submission
        setTimeout(() => {
            clearInterval(interval);
            progressBar.style.width = '100%';
        }, 2000);
    });
});
</script>

<?php include 'footer.php'; ?>
