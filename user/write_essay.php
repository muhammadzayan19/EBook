<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/db.php';

$comp_id = isset($_GET['comp_id']) ? intval($_GET['comp_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($comp_id === 0) {
    header('Location: competition.php');
    exit();
}

// Get competition details
$stmt = $conn->prepare("SELECT * FROM competitions WHERE comp_id = ? AND status = 'active'");
$stmt->bind_param("i", $comp_id);
$stmt->execute();
$result = $stmt->get_result();
$competition = $result->fetch_assoc();
$stmt->close();

if (!$competition) {
    $_SESSION['error'] = "Competition not found or not active.";
    header('Location: competition.php');
    exit();
}

// Check if already submitted
$check_stmt = $conn->prepare("SELECT submission_id FROM submissions WHERE comp_id = ? AND user_id = ?");
$check_stmt->bind_param("ii", $comp_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$has_submitted = $check_result->num_rows > 0;
$check_stmt->close();

if ($has_submitted) {
    $_SESSION['error'] = "You have already submitted an entry for this competition.";
    header('Location: competition.php?id=' . $comp_id);
    exit();
}

// Check if user has started (for timer)
$session_key = 'comp_start_' . $comp_id;
if (!isset($_SESSION[$session_key])) {
    $_SESSION[$session_key] = time();
}

$start_time = $_SESSION[$session_key];
$time_limit = 3 * 60 * 60; // 3 hours in seconds
$elapsed_time = time() - $start_time;
$remaining_time = max(0, $time_limit - $elapsed_time);

$page_title = "Write Your Entry - " . htmlspecialchars($competition['title']);
include '../includes/header.php';
?>

<style>
.writing-container {
    background: #f8f9fa;
    min-height: 100vh;
    padding: 2rem 0;
}

.writing-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
}

.writing-header h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.75rem;
    font-weight: 700;
}

.writing-header .comp-topic {
    background: rgba(255, 255, 255, 0.2);
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.writing-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.25rem;
    border-radius: 12px;
    border: 2px solid var(--border-color);
    text-align: center;
    transition: all 0.3s ease;
}

.stat-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.85rem;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-color);
}

.stat-value.warning {
    color: #f59e0b;
}

.stat-value.danger {
    color: #ef4444;
}

.editor-container {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    overflow: hidden;
    border: 2px solid var(--border-color);
}

.editor-toolbar {
    background: #f8f9fa;
    padding: 1rem;
    border-bottom: 2px solid var(--border-color);
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.toolbar-btn {
    padding: 0.5rem 1rem;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.toolbar-btn:hover {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.toolbar-btn.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.editor-content {
    padding: 2rem;
    min-height: 500px;
}

#essay-editor {
    width: 100%;
    min-height: 500px;
    border: none;
    outline: none;
    font-family: 'Georgia', serif;
    font-size: 1.1rem;
    line-height: 1.8;
    color: var(--text-color);
    resize: vertical;
}

.auto-save-indicator {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    background: white;
    padding: 1rem 1.5rem;
    border-radius: 50px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    z-index: 1000;
}

.save-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid var(--border-color);
    border-top-color: var(--primary-color);
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.action-buttons {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-submit {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
}

.btn-save-draft {
    background: white;
    color: var(--text-color);
    padding: 1rem 2rem;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-save-draft:hover {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.guidelines-sidebar {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    border: 2px solid var(--border-color);
    position: sticky;
    top: 2rem;
}

.guidelines-sidebar h5 {
    margin-bottom: 1rem;
    color: var(--text-color);
    font-weight: 700;
}

.guidelines-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.guidelines-list li {
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: start;
    gap: 0.75rem;
}

.guidelines-list li:last-child {
    border-bottom: none;
}

.guidelines-list i {
    color: var(--primary-color);
    margin-top: 0.25rem;
}

@media (max-width: 768px) {
    .writing-stats {
        grid-template-columns: 1fr 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .guidelines-sidebar {
        position: static;
        margin-top: 2rem;
    }
}
</style>

<div class="writing-container">
    <div class="container">
        <div class="writing-header">
            <h2>
                <i class="bi bi-pencil-square me-2"></i>
                <?php echo htmlspecialchars($competition['title']); ?>
            </h2>
            <small>
                <i class="bi bi-tag me-1"></i>
                <?php echo ucfirst($competition['type']); ?> Competition
            </small>
            <div class="comp-topic">
                <strong><i class="bi bi-lightbulb me-2"></i>Topic:</strong>
                <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($competition['topic'])); ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-9">
                <div class="writing-stats">
                    <div class="stat-card">
                        <div class="stat-label">
                            <i class="bi bi-clock-history me-1"></i>Time Remaining
                        </div>
                        <div class="stat-value" id="timer-display">
                            <?php echo gmdate('H:i:s', $remaining_time); ?>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-label">
                            <i class="bi bi-bar-chart me-1"></i>Word Count
                        </div>
                        <div class="stat-value" id="word-count">0</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-label">
                            <i class="bi bi-textarea-t me-1"></i>Character Count
                        </div>
                        <div class="stat-value" id="char-count">0</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-label">
                            <i class="bi bi-book me-1"></i>Est. Reading Time
                        </div>
                        <div class="stat-value" id="reading-time">0 min</div>
                    </div>
                </div>

                <div class="editor-container">
                    <div class="editor-toolbar">
                        <button class="toolbar-btn" onclick="formatText('bold')" title="Bold (Ctrl+B)">
                            <i class="bi bi-type-bold"></i> Bold
                        </button>
                        <button class="toolbar-btn" onclick="formatText('italic')" title="Italic (Ctrl+I)">
                            <i class="bi bi-type-italic"></i> Italic
                        </button>
                        <button class="toolbar-btn" onclick="formatText('underline')" title="Underline (Ctrl+U)">
                            <i class="bi bi-type-underline"></i> Underline
                        </button>
                        <button class="toolbar-btn" onclick="insertHeading()" title="Heading">
                            <i class="bi bi-type-h1"></i> Heading
                        </button>
                        <button class="toolbar-btn" onclick="clearFormatting()" title="Clear Formatting">
                            <i class="bi bi-eraser"></i> Clear Format
                        </button>
                    </div>
                    
                    <div class="editor-content">
                        <div id="essay-editor" contenteditable="true" placeholder="Start writing your masterpiece here...">
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn-submit" onclick="submitEntry()">
                        <i class="bi bi-send-fill"></i>
                        Submit Entry
                    </button>
                    <button type="button" class="btn-save-draft" onclick="saveDraft()">
                        <i class="bi bi-save"></i>
                        Save Draft
                    </button>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="guidelines-sidebar">
                    <h5>
                        <i class="bi bi-info-circle me-2"></i>
                        Writing Tips
                    </h5>
                    <ul class="guidelines-list">
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Write clearly and concisely</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Stay on topic throughout</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Use proper grammar and punctuation</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Structure your content well</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Proofread before submitting</span>
                        </li>
                        <li>
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Auto-save is active</span>
                        </li>
                    </ul>

                    <div class="mt-4 p-3 bg-light rounded">
                        <small class="text-muted">
                            <i class="bi bi-shield-check me-1"></i>
                            Your work is automatically saved every 30 seconds
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="auto-save-indicator" id="save-indicator" style="display: none;">
    <div class="save-spinner"></div>
    <span id="save-text">Saving...</span>
</div>

<script>
const compId = <?php echo $comp_id; ?>;
let remainingTime = <?php echo $remaining_time; ?>;
let autoSaveInterval;
let timerInterval;

// Initialize editor
const editor = document.getElementById('essay-editor');

// Add placeholder behavior
editor.addEventListener('focus', function() {
    if (this.textContent.trim() === '') {
        this.textContent = '';
    }
});

editor.addEventListener('blur', function() {
    if (this.textContent.trim() === '') {
        this.setAttribute('placeholder', 'Start writing your masterpiece here...');
    }
});

// Update statistics
editor.addEventListener('input', function() {
    updateStats();
});

function updateStats() {
    const text = editor.innerText.trim();
    const words = text.split(/\s+/).filter(word => word.length > 0);
    const chars = text.length;
    const readingTime = Math.ceil(words.length / 200); // Average reading speed

    document.getElementById('word-count').textContent = words.length;
    document.getElementById('char-count').textContent = chars;
    document.getElementById('reading-time').textContent = readingTime + ' min';
}

// Text formatting functions
function formatText(command) {
    document.execCommand(command, false, null);
    editor.focus();
}

function insertHeading() {
    document.execCommand('formatBlock', false, '<h3>');
    editor.focus();
}

function clearFormatting() {
    document.execCommand('removeFormat', false, null);
    editor.focus();
}

// Keyboard shortcuts
editor.addEventListener('keydown', function(e) {
    if (e.ctrlKey || e.metaKey) {
        switch(e.key) {
            case 'b':
                e.preventDefault();
                formatText('bold');
                break;
            case 'i':
                e.preventDefault();
                formatText('italic');
                break;
            case 'u':
                e.preventDefault();
                formatText('underline');
                break;
        }
    }
});

// Timer
function updateTimer() {
    if (remainingTime <= 0) {
        clearInterval(timerInterval);
        alert('Time is up! Your work has been auto-saved. Please submit your entry.');
        document.getElementById('timer-display').textContent = 'Time Up!';
        document.getElementById('timer-display').classList.add('danger');
        return;
    }

    const hours = Math.floor(remainingTime / 3600);
    const minutes = Math.floor((remainingTime % 3600) / 60);
    const seconds = remainingTime % 60;

    const timerDisplay = document.getElementById('timer-display');
    timerDisplay.textContent = 
        String(hours).padStart(2, '0') + ':' + 
        String(minutes).padStart(2, '0') + ':' + 
        String(seconds).padStart(2, '0');

    if (remainingTime < 600) { // Less than 10 minutes
        timerDisplay.classList.add('danger');
    } else if (remainingTime < 1800) { // Less than 30 minutes
        timerDisplay.classList.add('warning');
    }

    remainingTime--;
}

timerInterval = setInterval(updateTimer, 1000);

// Auto-save functionality
function showSaveIndicator(text) {
    const indicator = document.getElementById('save-indicator');
    const saveText = document.getElementById('save-text');
    saveText.textContent = text;
    indicator.style.display = 'flex';
    
    setTimeout(() => {
        indicator.style.display = 'none';
    }, 2000);
}

function saveDraft() {
    const content = editor.innerHTML;
    
    if (content.trim() === '') {
        alert('Please write something before saving.');
        return;
    }

    showSaveIndicator('Saving draft...');

    const formData = new FormData();
    formData.append('action', 'save_draft');
    formData.append('comp_id', compId);
    formData.append('content', content);

    fetch('../includes/handle_submission.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSaveIndicator('Draft saved ✓');
        } else {
            showSaveIndicator('Save failed ✗');
            console.error(data.message);
        }
    })
    .catch(error => {
        showSaveIndicator('Save failed ✗');
        console.error('Error:', error);
    });
}

// Auto-save every 30 seconds
autoSaveInterval = setInterval(() => {
    const content = editor.innerHTML;
    if (content.trim() !== '') {
        saveDraft();
    }
}, 30000);

// Load saved draft on page load
window.addEventListener('load', function() {
    const formData = new FormData();
    formData.append('action', 'load_draft');
    formData.append('comp_id', compId);

    fetch('../includes/handle_submission.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.content) {
            editor.innerHTML = data.content;
            updateStats();
        }
    })
    .catch(error => console.error('Error loading draft:', error));
});

// Submit entry
function submitEntry() {
    const content = editor.innerHTML;
    const text = editor.innerText.trim();

    if (text === '') {
        alert('Please write your entry before submitting.');
        return;
    }

    if (text.split(/\s+/).length < 100) {
        if (!confirm('Your entry seems short (less than 100 words). Are you sure you want to submit?')) {
            return;
        }
    }

    if (!confirm('Are you sure you want to submit? You cannot edit after submission.')) {
        return;
    }

    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

    const formData = new FormData();
    formData.append('action', 'submit_entry');
    formData.append('comp_id', compId);
    formData.append('content', content);

    fetch('../includes/handle_submission.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Entry submitted successfully!');
            window.location.href = 'competition.php?id=' + compId;
        } else {
            alert('Error: ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill"></i> Submit Entry';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-fill"></i> Submit Entry';
    });
}

// Warn before leaving
window.addEventListener('beforeunload', function(e) {
    const content = editor.innerText.trim();
    if (content !== '') {
        e.preventDefault();
        e.returnValue = '';
        return 'You have unsaved changes. Are you sure you want to leave?';
    }
});

// Initial stats update
updateStats();
</script>

<?php include '../includes/footer.php'; ?>