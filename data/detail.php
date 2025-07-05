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

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title mb-0">🎵 <?php echo htmlspecialchars($recording['name']); ?></h2>
            <p class="card-subtitle">Recording Details & Information</p>
        </div>
        
        <div class="card-body">
            <div class="grid grid-cols-2">
                <!-- Basic Information -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">📋 Basic Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong class="text-muted">ID:</strong><br>
                            <span class="text-muted">#<?php echo $recording['id']; ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <strong class="text-muted">File Path:</strong><br>
                            <code><?php echo htmlspecialchars($recording['file_path']); ?></code>
                        </div>
                        
                        <div class="mb-3">
                            <strong class="text-muted">File Size:</strong><br>
                            <?php 
                            $size = $recording['file_size'];
                            if ($size > 1024*1024) {
                                echo number_format($size / (1024*1024), 2) . ' MB';
                            } elseif ($size > 1024) {
                                echo number_format($size / 1024, 2) . ' KB';
                            } else {
                                echo $size . ' Bytes';
                            }
                            ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong class="text-muted">Uploaded:</strong><br>
                            <time datetime="<?php echo $recording['created_at']; ?>">
                                <?php echo date('F j, Y \\a\\t g:i A', strtotime($recording['created_at'])); ?>
                            </time>
                        </div>
                        
                        <div class="mb-0">
                            <strong class="text-muted">MD5 Hash:</strong><br>
                            <code style="font-size: 0.75rem; word-break: break-all;"><?php echo $recording['md5_hash']; ?></code>
                        </div>
                    </div>
                </div>
                
                <!-- Metadata -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">🏷️ Metadata</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong class="text-muted">Tags:</strong><br>
                            <?php if (!empty($tags)): ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
                                    <?php foreach ($tags as $tag): ?>
                                        <span style="background: var(--primary-color); color: white; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-size: 0.75rem;">
                                            🏷️ <?php echo htmlspecialchars($tag); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">No tags assigned</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong class="text-muted">Locations:</strong><br>
                            <?php if (!empty($locations)): ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
                                    <?php foreach ($locations as $location): ?>
                                        <span style="background: var(--success-color); color: white; padding: 0.25rem 0.75rem; border-radius: var(--radius-sm); font-size: 0.75rem;">
                                            📍 <?php echo htmlspecialchars($location); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">No locations assigned</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-0">
                            <strong class="text-muted">Notes:</strong><br>
                            <?php if (!empty($recording['notes'])): ?>
                                <div style="background: var(--background-color); padding: 1rem; border-radius: var(--radius-md); margin-top: 0.5rem;">
                                    <?php echo nl2br(htmlspecialchars($recording['notes'])); ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">No notes available</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Audio Player Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">🎧 Audio Player</h3>
                </div>
                <div class="card-body" style="padding: 2rem;">
                    <?php 
                    $webPath = str_replace(__DIR__ . '/', '', $recording['file_path']);
                    $fileExt = strtolower(pathinfo($recording['file_path'], PATHINFO_EXTENSION));
                    
                    // Use audio serving script for range request support
                    $audioUrl = 'serve_audio.php?file=' . urlencode($webPath);
                    
                    // Determine MIME type based on file extension
                    $mimeTypes = [
                        'mp3' => 'audio/mpeg',
                        'wav' => 'audio/wav',
                        'flac' => 'audio/flac',
                        'ogg' => 'audio/ogg',
                        'm4a' => 'audio/mp4',
                        'aac' => 'audio/aac',
                        'wma' => 'audio/x-ms-wma',
                        'aiff' => 'audio/aiff',
                        'au' => 'audio/basic'
                    ];
                    $mimeType = isset($mimeTypes[$fileExt]) ? $mimeTypes[$fileExt] : 'audio/mpeg';
                    ?>
                    
                    <!-- Clean Player Container -->
                    <div style="background: linear-gradient(135deg, var(--surface-color) 0%, var(--background-color) 100%); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border: 1px solid rgba(0,0,0,0.05);">
                        
                        <!-- Track Info -->
                        <div style="text-align: center; margin-bottom: 1.5rem;">
                            <h4 style="margin: 0 0 0.5rem 0; color: var(--text-color); font-weight: 600;"><?php echo htmlspecialchars($recording['name']); ?></h4>
                            <div style="display: flex; justify-content: center; align-items: center; gap: 1rem; color: var(--text-muted); font-size: 0.875rem;">
                                <span>🎵 <?php echo strtoupper($fileExt); ?></span>
                                <span>•</span>
                                <span id="audioInfo">
                                    <span id="currentTime">0:00</span> / <span id="duration">--:--</span>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Audio Player -->
                        <audio id="audioPlayer" controls preload="auto" style="width: 100%; height: 60px; border-radius: 8px; outline: none;" controlsList="nodownload">
                            <source src="<?php echo htmlspecialchars($audioUrl); ?>" type="<?php echo $mimeType; ?>">
                            <!-- Fallback with direct path if range requests fail -->
                            <source src="<?php echo htmlspecialchars($webPath); ?>" type="<?php echo $mimeType; ?>">
                            Your browser does not support the audio element.
                        </audio>
                        
                        <!-- Keyboard Shortcuts -->
                        <div style="margin-top: 1.5rem; text-align: center;">
                            <div style="display: inline-flex; gap: 1.5rem; flex-wrap: wrap; justify-content: center; font-size: 0.75rem; color: var(--text-muted);">
                                <span style="background: rgba(0,0,0,0.05); padding: 0.25rem 0.5rem; border-radius: 4px;">⏯️ Space</span>
                                <span style="background: rgba(0,0,0,0.05); padding: 0.25rem 0.5rem; border-radius: 4px;">⏪ ← 5s</span>
                                <span style="background: rgba(0,0,0,0.05); padding: 0.25rem 0.5rem; border-radius: 4px;">⏩ → 5s</span>
                                <span style="background: rgba(0,0,0,0.05); padding: 0.25rem 0.5rem; border-radius: 4px;">🔊 ↑↓</span>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const audio = document.getElementById('audioPlayer');
                        const currentTimeSpan = document.getElementById('currentTime');
                        const durationSpan = document.getElementById('duration');
                        
                        // Format time helper function
                        function formatTime(seconds) {
                            if (isNaN(seconds)) return '--:--';
                            const mins = Math.floor(seconds / 60);
                            const secs = Math.floor(seconds % 60);
                            return mins + ':' + (secs < 10 ? '0' : '') + secs;
                        }
                        
                        // Update duration when metadata loads - multiple events for reliability
                        function updateDuration() {
                            if (audio.duration && !isNaN(audio.duration) && isFinite(audio.duration)) {
                                durationSpan.textContent = formatTime(audio.duration);
                                console.log('Duration updated:', audio.duration);
                            }
                        }
                        
                        audio.addEventListener('loadedmetadata', updateDuration);
                        audio.addEventListener('durationchange', updateDuration);
                        audio.addEventListener('canplay', updateDuration);
                        audio.addEventListener('loadeddata', function() {
                            updateDuration();
                            console.log('Audio data loaded, duration:', audio.duration);
                            console.log('Audio seekable ranges:', audio.seekable.length);
                            if (audio.seekable.length > 0) {
                                console.log('Seekable from', audio.seekable.start(0), 'to', audio.seekable.end(0));
                            }
                        });
                        
                        // Update current time during playback
                        audio.addEventListener('timeupdate', function() {
                            currentTimeSpan.textContent = formatTime(audio.currentTime);
                        });
                        
                        // Keyboard controls for better seeking
                        document.addEventListener('keydown', function(e) {
                            if (document.activeElement.tagName.toLowerCase() === 'input' || 
                                document.activeElement.tagName.toLowerCase() === 'textarea') {
                                return; // Don't interfere with form inputs
                            }
                            
                            switch(e.code) {
                                case 'Space':
                                    e.preventDefault();
                                    if (audio.paused) {
                                        audio.play();
                                    } else {
                                        audio.pause();
                                    }
                                    break;
                                case 'ArrowLeft':
                                    e.preventDefault();
                                    audio.currentTime = Math.max(0, audio.currentTime - 5);
                                    break;
                                case 'ArrowRight':
                                    e.preventDefault();
                                    audio.currentTime = Math.min(audio.duration, audio.currentTime + 5);
                                    break;
                                case 'ArrowUp':
                                    e.preventDefault();
                                    audio.volume = Math.min(1, audio.volume + 0.1);
                                    break;
                                case 'ArrowDown':
                                    e.preventDefault();
                                    audio.volume = Math.max(0, audio.volume - 0.1);
                                    break;
                            }
                        });
                        
                        // Debug and error handling
                        audio.addEventListener('loadstart', function() {
                            console.log('Audio loading started');
                        });
                        
                        audio.addEventListener('canplay', function() {
                            console.log('Audio can start playing');
                        });
                        
                        audio.addEventListener('canplaythrough', function() {
                            console.log('Audio can play through without buffering');
                        });
                        
                        audio.addEventListener('error', function(e) {
                            console.error('Audio error:', e);
                            console.error('Audio error details:', audio.error);
                        });
                        
                        // Ensure seeking works properly
                        audio.addEventListener('seeking', function() {
                            console.log('Seeking to:', audio.currentTime);
                        });
                        
                        audio.addEventListener('seeked', function() {
                            console.log('Seeked to:', audio.currentTime);
                        });
                        
                        // Force seeking capability - handled above in loadeddata event
                         
                         // Manual progress bar click handler for seeking
                         audio.addEventListener('click', function(e) {
                             const rect = audio.getBoundingClientRect();
                             const clickX = e.clientX - rect.left;
                             const width = rect.width;
                             const clickRatio = clickX / width;
                             const newTime = clickRatio * audio.duration;
                             
                             if (!isNaN(newTime) && newTime >= 0 && newTime <= audio.duration) {
                                 audio.currentTime = newTime;
                                 console.log('Manual seek to:', newTime);
                             }
                         });
                    });
                    </script>
                </div>
            </div>
        </div>
        
        <div class="card-footer">
            <div class="text-center">
                <a href="<?php echo htmlspecialchars($webPath); ?>" download class="btn btn-primary">
                    💾 Download Audio File
                </a>
                <a href="export.php?id=<?php echo $recording['id']; ?>" class="btn btn-secondary">
                    📄 Download Metadata
                </a>
                <a href="edit.php?id=<?php echo $recording['id']; ?>" class="btn btn-secondary">
                    ✏️ Edit Recording
                </a>
                <a href="index.php" class="btn btn-secondary">
                    ← Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
