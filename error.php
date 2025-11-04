<?php
// Get error details from URL parameters or set defaults
$error_code = isset($_GET['code']) ? htmlspecialchars($_GET['code']) : '404';
$error_message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Page Not Found';
$error_description = isset($_GET['description']) ? htmlspecialchars($_GET['description']) : 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.';

// Set appropriate HTTP response code
http_response_code($error_code);

// Define error types and their icons
$error_types = [
    '400' => ['icon' => 'bi-exclamation-triangle', 'title' => 'Bad Request', 'color' => 'warning'],
    '401' => ['icon' => 'bi-lock', 'title' => 'Unauthorized', 'color' => 'danger'],
    '403' => ['icon' => 'bi-shield-exclamation', 'title' => 'Forbidden', 'color' => 'danger'],
    '404' => ['icon' => 'bi-search', 'title' => 'Page Not Found', 'color' => 'primary'],
    '500' => ['icon' => 'bi-exclamation-octagon', 'title' => 'Internal Server Error', 'color' => 'danger'],
    '503' => ['icon' => 'bi-tools', 'title' => 'Service Unavailable', 'color' => 'warning'],
];

$current_error = isset($error_types[$error_code]) ? $error_types[$error_code] : $error_types['404'];
$page_title = $error_code . ' - ' . $current_error['title'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo $page_title; ?> | Online E-Book System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/images/ZeBook.webp" type="image/webp">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .error-page {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--bg-color) 0%, #e5e7eb 100%);
            padding: 2rem 0;
            position: relative;
            overflow: hidden;
        }

        .error-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(37, 99, 235, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(14, 165, 233, 0.03) 0%, transparent 50%);
            pointer-events: none;
        }

        .error-container {
            position: relative;
            z-index: 1;
            max-width: 700px;
            width: 100%;
            padding: 0 1rem;
        }

        .error-card {
            background: var(--white);
            border-radius: 24px;
            padding: 3rem 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
            text-align: center;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-icon-wrapper {
            width: 140px;
            height: 140px;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3);
            position: relative;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-15px);
            }
        }

        .error-icon-wrapper::before {
            content: '';
            position: absolute;
            inset: -10px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            opacity: 0.1;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.15;
            }
        }

        .error-icon-wrapper i {
            font-size: 4.5rem;
            color: var(--white);
            position: relative;
            z-index: 1;
        }

        .error-code {
            font-size: 5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 1rem;
            letter-spacing: -2px;
        }

        .error-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }

        .error-description {
            font-size: 1.1rem;
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 2.5rem;
        }

        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .error-actions .btn {
            padding: 0.875rem 2rem;
            font-weight: 600;
            border-radius: 12px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            color: var(--white);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            color: var(--white);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        .error-suggestions {
            background: var(--bg-color);
            border-radius: 16px;
            padding: 2rem;
            margin-top: 2.5rem;
            text-align: left;
            border: 1px solid var(--border-color);
        }

        .error-suggestions h5 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .error-suggestions h5 i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .suggestions-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .suggestions-list li {
            padding: 0.75rem 0 0.75rem 2rem;
            position: relative;
            color: var(--text-color);
            line-height: 1.6;
            border-bottom: 1px solid var(--border-color);
        }

        .suggestions-list li:last-child {
            border-bottom: none;
        }

        .suggestions-list li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .suggestions-list a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .suggestions-list a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .error-card {
                padding: 2.5rem 1.5rem;
            }

            .error-icon-wrapper {
                width: 120px;
                height: 120px;
            }

            .error-icon-wrapper i {
                font-size: 3.5rem;
            }

            .error-code {
                font-size: 4rem;
            }

            .error-title {
                font-size: 1.65rem;
            }

            .error-description {
                font-size: 1rem;
            }

            .error-actions {
                flex-direction: column;
            }

            .error-actions .btn {
                width: 100%;
            }

            .error-suggestions {
                padding: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .error-card {
                padding: 2rem 1.25rem;
                border-radius: 20px;
            }

            .error-icon-wrapper {
                width: 100px;
                height: 100px;
            }

            .error-icon-wrapper i {
                font-size: 3rem;
            }

            .error-code {
                font-size: 3rem;
            }

            .error-title {
                font-size: 1.5rem;
            }

            .error-description {
                font-size: 0.95rem;
            }

            .error-suggestions h5 {
                font-size: 1.1rem;
            }

            .suggestions-list li {
                font-size: 0.9rem;
                padding-left: 1.75rem;
            }
        }

        /* Print Styles */
        @media print {
            .error-page::before,
            .error-icon-wrapper::before {
                display: none;
            }

            .error-card {
                box-shadow: none;
                border: 1px solid var(--border-color);
            }

            .error-actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-container">
            <div class="error-card">
                <div class="error-icon-wrapper">
                    <i class="bi <?php echo $current_error['icon']; ?>"></i>
                </div>

                <div class="error-code"><?php echo $error_code; ?></div>
                <h1 class="error-title"><?php echo $error_message; ?></h1>
                <p class="error-description"><?php echo $error_description; ?></p>

                <div class="error-actions">
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-house-door"></i>
                        Back to Home
                    </a>
                    <button onclick="history.back()" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left"></i>
                        Go Back
                    </button>
                </div>

                <div class="error-suggestions">
                    <h5>
                        <i class="bi bi-lightbulb"></i>
                        Here's what you can do:
                    </h5>
                    <ul class="suggestions-list">
                        <li>Check the URL for typos and try again</li>
                        <li>Visit our <a href="index.php">homepage</a> to start fresh</li>
                        <li>Browse our <a href="user/books.php">books collection</a></li>
                        <li>Check out ongoing <a href="user/competition.php">competitions</a></li>
                        <li>Contact our <a href="contact.php">support team</a> if the problem persists</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>