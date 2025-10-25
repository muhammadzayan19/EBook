<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

require_once '../config/db.php';

$comp_id = isset($_GET['comp_id']) ? intval($_GET['comp_id']) : 0;

if ($comp_id === 0) {
    header('Location: competition.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM competitions WHERE comp_id = ? AND status = 'active'");
$stmt->bind_param("i", $comp_id);
$stmt->execute();
$result = $stmt->get_result();
$competition = $result->fetch_assoc();
$stmt->close();

if (!$competition) {
    header('Location: competition.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$check_stmt = $conn->prepare("SELECT submission_id FROM submissions WHERE comp_id = ? AND user_id = ?");
$check_stmt->bind_param("ii", $comp_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$already_submitted = $check_result->num_rows > 0;
$check_stmt->close();

if ($already_submitted) {
    header('Location: competition.php?id=' . $comp_id);
    exit();
}

$page_title = "Upload Entry - " . $competition['title'];

include '../includes/header.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_dir = '../uploads/essays/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    if (isset($_FILES['essay_file']) && $_FILES['essay_file']['error'] === 0) {
        $file = $_FILES['essay_file'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed = ['doc', 'docx', 'pdf'];
        
        if (!in_array($file_ext, $allowed)) {
            $error_message = 'Invalid file format. Please upload .doc, .docx, or .pdf files only.';
        } elseif ($file_size > 5 * 1024 * 1024) { 
            $error_message = 'File size exceeds 5MB limit.';
        } else {
            $new_filename = 'essay_' . $comp_id . '_' . $user_id . '_' . time() . '.' . $file_ext;
            $file_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                $insert_stmt = $conn->prepare("INSERT INTO submissions (comp_id, user_id, file_path, submitted_at) VALUES (?, ?, ?, NOW())");
                $db_file_path = 'uploads/essays/' . $new_filename;
                $insert_stmt->bind_param("iis", $comp_id, $user_id, $db_file_path);
                
                if ($insert_stmt->execute()) {
                    $success_message = 'Your entry has been submitted successfully!';
                    $already_submitted = true;
                } else {
                    $error_message = 'Failed to save submission. Please try again.';
                    unlink($file_path);
                }
                $insert_stmt->close();
            } else {
                $error_message = 'Failed to upload file. Please try again.';
            }
        }
    } else {
        $error_message = 'Please select a file to upload.';
    }
}

$timer_started = isset($_SESSION['timer_start_' . $comp_id]);

if (isset($_POST['start_timer']) && !$timer_started) {
    $_SESSION['timer_start_' . $comp_id] = time();
    $timer_started = true;
}

if ($timer_started) {
    $timer_start = $_SESSION['timer_start_' . $comp_id];
    $time_limit = 3 * 60 * 60;
    $time_elapsed = time() - $timer_start;
    $time_remaining = max(0, $time_limit - $time_elapsed);
} else {
    $time_remaining = 3 * 60 * 60;
}
?>

<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto text-center text-white">
                <h1 class="display-5 fw-bold mb-3">Upload Your Entry</h1>
                <p class="lead mb-4"><?php echo htmlspecialchars($competition['title']); ?></p>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="competition.php">Competitions</a></li>
                        <li class="breadcrumb-item active">Upload Entry</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<section class="upload-essay-section py-5">
    <div class="container">
        <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <div class="text-center mb-4">
            <a href="competition.php?id=<?php echo $comp_id; ?>" class="btn btn-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Competition
            </a>
        </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (!$already_submitted && !$timer_started): ?>
            <div class="start-competition-card mb-4">
                <div class="text-center py-5">
                    <i class="bi bi-clock-history" style="font-size: 4rem; color: var(--primary-color);"></i>
                    <h4 class="mt-4 mb-3">Ready to Start?</h4>
                    <p class="text-muted mb-4">Once you start, you'll have 3 hours to complete and submit your entry.</p>
                    <form method="POST">
                        <button type="submit" name="start_timer" class="btn btn-primary btn-lg">
                            <i class="bi bi-play-circle me-2"></i>Start Competition
                        </button>
                    </form>
                </div>
            </div>
            
        <?php elseif ($timer_started && !$already_submitted): ?>
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="upload-card">
                    <div class="upload-header">
                        <h4 class="upload-title">
                            <i class="bi bi-cloud-upload me-2"></i>Submit Your Entry
                        </h4>
                        <p class="text-muted">Upload your essay or story in .doc, .docx, or .pdf format (Max 5MB)</p>
                    </div>
                    
                    <div class="timer-display" id="timerDisplay">
                        <div class="timer-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="timer-content">
                            <h5 class="timer-label">Time Remaining</h5>
                            <div class="timer-value" id="timer">
                                <span id="hours">03</span>:<span id="minutes">00</span>:<span id="seconds">00</span>
                            </div>
                            <div class="timer-warning" id="timerWarning" style="display: none;">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <span>Hurry! Less than 30 minutes remaining</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="topic-display">
                        <h5 class="topic-title">
                            <i class="bi bi-lightbulb me-2"></i>Competition Topic
                        </h5>
                        <div class="topic-content">
                            <?php echo nl2br(htmlspecialchars($competition['topic'])); ?>
                        </div>
                    </div>
                    
                    <form method="POST" action="" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                        <div class="file-upload-wrapper">
                            <input type="file" name="essay_file" id="essayFile" class="file-input" 
                                   accept=".doc,.docx,.pdf" required>
                            <label for="essayFile" class="file-label">
                                <div class="file-label-content">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                    <h5>Choose File or Drag & Drop</h5>
                                    <p>Supported formats: DOC, DOCX, PDF (Max 5MB)</p>
                                    <span class="btn btn-primary">Browse Files</span>
                                </div>
                            </label>
                            <div class="file-selected" id="fileSelected" style="display: none;">
                                <div class="file-info">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <div class="file-details">
                                        <span class="file-name" id="fileName"></span>
                                        <span class="file-size" id="fileSize"></span>
                                    </div>
                                </div>
                                <button type="button" class="btn-remove" onclick="removeFile()">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="upload-guidelines">
                            <h6><i class="bi bi-info-circle me-2"></i>Submission Guidelines</h6>
                            <ul>
                                <li>Ensure your work is original and plagiarism-free</li>
                                <li>Follow the word count requirements if specified</li>
                                <li>Proofread your work before submission</li>
                                <li>File name should not contain special characters</li>
                                <li>Once submitted, you cannot modify your entry</li>
                            </ul>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                            <label class="form-check-label" for="agreeTerms">
                                I confirm that this is my original work and I agree to the 
                                <a href="#" class="text-primary">competition terms and conditions</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                            <i class="bi bi-send me-2"></i>Submit Entry
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="upload-sidebar">
                    <div class="sidebar-card">
                        <h6 class="sidebar-title">
                            <i class="bi bi-trophy me-2"></i>Competition Details
                        </h6>
                        <div class="sidebar-info">
                            <div class="info-item">
                                <span class="info-label">Type</span>
                                <span class="info-value badge-type"><?php echo ucfirst($competition['type']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Prize</span>
                                <span class="info-value text-success fw-bold"><?php echo htmlspecialchars($competition['prize']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Deadline</span>
                                <span class="info-value"><?php echo date('M j, Y', strtotime($competition['end_date'])); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Time Limit</span>
                                <span class="info-value">3 Hours</span>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-card mt-4">
                        <h6 class="sidebar-title">
                            <i class="bi bi-lightbulb me-2"></i>Writing Tips
                        </h6>
                        <ul class="tips-list">
                            <li>Plan your structure before writing</li>
                            <li>Stay focused on the topic</li>
                            <li>Use clear and concise language</li>
                            <li>Proofread for grammar and spelling</li>
                            <li>Make your conclusion impactful</li>
                        </ul>
                    </div>
                    
                    <div class="sidebar-card mt-4">
                        <h6 class="sidebar-title">
                            <i class="bi bi-question-circle me-2"></i>Need Help?
                        </h6>
                        <p class="small text-muted mb-3">Having trouble uploading? Check our FAQ or contact support.</p>
                        <a href="../contact.php" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-headset me-2"></i>Contact Support
                        </a>
                    </div>
                    
                    <div class="reminder-card mt-4">
                        <i class="bi bi-info-circle-fill"></i>
                        <div>
                            <strong>Important Reminder</strong>
                            <p>Save your work frequently! Timer continues even if you close this page.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
let timeRemaining = <?php echo $time_remaining; ?>;
const timerInterval = setInterval(updateTimer, 1000);

function updateTimer() {
    if (timeRemaining <= 0) {
        clearInterval(timerInterval);
        document.getElementById('timer').innerHTML = '<span class="text-danger">Time Expired!</span>';
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('uploadForm').innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Time limit has expired. You can no longer submit your entry.</div>';
        return;
    }
    
    const hours = Math.floor(timeRemaining / 3600);
    const minutes = Math.floor((timeRemaining % 3600) / 60);
    const seconds = timeRemaining % 60;
    
    document.getElementById('hours').textContent = String(hours).padStart(2, '0');
    document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
    document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
    
    if (timeRemaining <= 1800 && timeRemaining > 0) {
        document.getElementById('timerWarning').style.display = 'flex';
        document.getElementById('timerDisplay').classList.add('timer-warning-active');
    }
    
    if (timeRemaining <= 600) {
        document.getElementById('timer').classList.add('timer-critical');
    }
    
    timeRemaining--;
}

const fileInput = document.getElementById('essayFile');
const fileLabel = document.querySelector('.file-label-content');
const fileSelected = document.getElementById('fileSelected');

fileInput.addEventListener('change', handleFileSelect);

const fileUploadWrapper = document.querySelector('.file-upload-wrapper');

fileUploadWrapper.addEventListener('dragover', (e) => {
    e.preventDefault();
    fileUploadWrapper.classList.add('drag-over');
});

fileUploadWrapper.addEventListener('dragleave', () => {
    fileUploadWrapper.classList.remove('drag-over');
});

fileUploadWrapper.addEventListener('drop', (e) => {
    e.preventDefault();
    fileUploadWrapper.classList.remove('drag-over');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        handleFileSelect();
    }
});

function handleFileSelect() {
    const file = fileInput.files[0];
    if (file) {
        const fileName = file.name;
        const fileSize = formatFileSize(file.size);
        const fileExt = fileName.split('.').pop().toLowerCase();
        
        const allowed = ['doc', 'docx', 'pdf'];
        if (!allowed.includes(fileExt)) {
            alert('Invalid file format. Please upload .doc, .docx, or .pdf files only.');
            fileInput.value = '';
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            alert('File size exceeds 5MB limit.');
            fileInput.value = '';
            return;
        }
        
        document.getElementById('fileName').textContent = fileName;
        document.getElementById('fileSize').textContent = fileSize;
        document.querySelector('.file-label-content').style.display = 'none';
        fileSelected.style.display = 'flex';
    }
}

function removeFile() {
    fileInput.value = '';
    document.querySelector('.file-label-content').style.display = 'flex';
    fileSelected.style.display = 'none';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

document.getElementById('uploadForm').addEventListener('submit', function(e) {
    if (timeRemaining <= 0) {
        e.preventDefault();
        alert('Time limit has expired!');
        return false;
    }
    
    if (!fileInput.files.length) {
        e.preventDefault();
        alert('Please select a file to upload.');
        return false;
    }
    
    if (!document.getElementById('agreeTerms').checked) {
        e.preventDefault();
        alert('Please agree to the terms and conditions.');
        return false;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
    submitBtn.disabled = true;
});

window.addEventListener('beforeunload', function(e) {
    if (!<?php echo $already_submitted ? 'true' : 'false'; ?> && timeRemaining > 0) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});
</script>

<?php
include '../includes/footer.php';
?>