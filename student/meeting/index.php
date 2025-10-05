<?php

    session_start();
    require_once "../../configDatabase.php";

    // Check if student is logged in
    if (!isset($_SESSION['typeStudent']) || !isset($_SESSION['idStudent'])) {
        header("location: ../index.php");
        die();
    }

    $accountId = $_SESSION['idStudent'];
    $studentEmail = $_SESSION['emailStudent'];
    $studentName = $_SESSION['fullNameStudent'];

    if (isset($_GET['meetingId'])) // testez daca e setat un meeting
        $meetingId = $_GET['meetingId'];
    else {
        header("location: ../index.php");
        die();
    }

    $sqlMeetingData = "SELECT * FROM meetings WHERE `meetingId` = '$meetingId'";    
    $queryMeetingData = mysqli_query($link, $sqlMeetingData);

    if (mysqli_num_rows($queryMeetingData) > 0) // testez daca exista un meeting cu id-ul dat
        $dataMeeting = mysqli_fetch_assoc($queryMeetingData);
    else {
        header("location: ../index.php");
        die();
    }

    $studentId = $dataMeeting['studentId'];
    $consultantId = $dataMeeting['consultantId'];

    if (!($studentId == $accountId)) { // testez daca are acces userul la acest meeting
        header("location: ../index.php");
        die();
    }

    $studentName = $dataMeeting['studentName'];
    $studentSchool = $dataMeeting['studentSchool'];
    $consultantName = $dataMeeting['consultantName'];
    $meetingDate = $dataMeeting['meetingDate'];
    $meetingNotes = $dataMeeting['meetingNotes'];
    $meetingTopic = $dataMeeting['meetingTopic'];
    $meetingActivities = $dataMeeting['meetingActivities'];
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Meeting Details</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-light: #818cf8;
            --secondary-color: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--text-primary);
        }

        .main-container {
            padding: 2rem 0;
            min-height: 100vh;
        }

        .meeting-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .meeting-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .meeting-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .meeting-header .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 400;
        }

        .meeting-content {
            padding: 2rem;
        }

        .info-section {
            margin-bottom: 2rem;
        }

        .info-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-item {
            background: var(--secondary-color);
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
        }

        .info-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .info-value a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .info-value a:hover {
            color: var(--primary-light);
        }

        .content-section {
            background: var(--secondary-color);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .content-text {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-primary);
            white-space: pre-wrap;
        }

        .empty-content {
            color: var(--text-secondary);
            font-style: italic;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn-custom {
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary-custom {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary-custom:hover {
            background: var(--primary-light);
            color: white;
            transform: translateY(-2px);
        }

        .btn-secondary-custom {
            background: var(--secondary-color);
            color: var(--text-primary);
            border: 2px solid var(--border-color);
        }

        .btn-secondary-custom:hover {
            background: white;
            color: var(--text-primary);
            transform: translateY(-2px);
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-completed {
            background: var(--success-color);
            color: white;
        }

        .status-pending {
            background: var(--warning-color);
            color: white;
        }

        @media (max-width: 768px) {
            .meeting-header h1 {
                font-size: 2rem;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .meeting-content {
                padding: 1.5rem;
            }
        }

        .navbar {
            height: 150px;
        }

        .badge {
            font-size: 15px;
            color: white;
            background-color: var(--pink) !important;
            position: absolute;
            right: 50%;
        }
        
        .fw-bold {
            font-weight: bold;
        }

        .statusSelect {
            width: 100px;
            height: 25px;
        }
    </style>
</head>

<body>
    <?php include("../navbarStudent.php"); ?>

    <div class="main-container">
        <div class="container">
            <div class="meeting-card">
                <div class="meeting-header">
                    <h1><i class="fas fa-calendar-check"></i> Meeting Details</h1>
                    <p class="subtitle">Review your consultation session information</p>
                </div>

                <div class="meeting-content">
                    <!-- Basic Information -->
                    <div class="info-section">
                        <h3 class="section-title">
                            <i class="fas fa-info-circle"></i>
                            Meeting Information
                        </h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Meeting Host</div>
                                <div class="info-value"><?php echo htmlspecialchars($consultantName); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Student Name</div>
                                <div class="info-value">
                                    <a href="../"><?php echo htmlspecialchars($studentName); ?></a>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Student School</div>
                                <div class="info-value"><?php echo htmlspecialchars($studentSchool); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Meeting Date & Time</div>
                                <div class="info-value"><?php echo htmlspecialchars($meetingDate); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Meeting Topic -->
                    <?php if (!empty($meetingTopic)): ?>
                    <div class="info-section">
                        <h3 class="section-title">
                            <i class="fas fa-lightbulb"></i>
                            Meeting Topic
                        </h3>
                        <div class="content-section">
                            <div class="content-text"><?php echo htmlspecialchars($meetingTopic); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Meeting Notes -->
                    <?php if (!empty($meetingNotes)): ?>
                    <div class="info-section">
                        <h3 class="section-title">
                            <i class="fas fa-sticky-note"></i>
                            Meeting Notes
                        </h3>
                        <div class="content-section">
                            <div class="content-text"><?php echo htmlspecialchars($meetingNotes); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Meeting Activities -->
                    <?php if (!empty($meetingActivities)): ?>
                    <div class="info-section">
                        <h3 class="section-title">
                            <i class="fas fa-tasks"></i>
                            Meeting Activities
                        </h3>
                        <div class="content-section">
                            <div class="content-text"><?php echo htmlspecialchars($meetingActivities); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="../" class="btn-custom btn-secondary-custom">
                            <i class="fas fa-arrow-left"></i>
                            Back to Dashboard
                        </a>
                        <button onclick="window.print()" class="btn-custom btn-primary-custom">
                            <i class="fas fa-print"></i>
                            Print Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function confirmRemove(link) {
            const userConfirmed = confirm("Are you sure you want to delete this meeting?");
            if (userConfirmed) {
                window.location.href = link;
            }
        }

        // Add smooth scrolling
        document.addEventListener('DOMContentLoaded', function() {
            // Add fade-in animation to cards
            const cards = document.querySelectorAll('.info-item, .content-section');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>