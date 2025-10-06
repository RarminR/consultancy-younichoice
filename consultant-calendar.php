<?php
    session_start();
    require_once "configDatabase.php";

    if (!isset($_SESSION['type'])) { // test if user is logged in
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $accountId = $_SESSION["id"];
    }

    // Only consultants can access this page
    if ($typeAccount != 2 && $typeAccount != 1) {
        header("location: index.php");
        die();
    }

    $consultantId = $accountId;
    
    // Get consultant data
    $sqlConsultantData = "SELECT * FROM users WHERE `userId` = '$consultantId'";
    $queryConsultantData = mysqli_query($link, $sqlConsultantData);
    
    if (mysqli_num_rows($queryConsultantData) > 0) {
        $dataConsultant = mysqli_fetch_assoc($queryConsultantData);
        $consultantName = $dataConsultant["fullName"];
        $consultantEmail = $dataConsultant["email"];
    } else {
        header("location: index.php");
        die();
    }
?>

<!DOCTYPE html><!--  This site was created in Webflow. https://www.webflow.com  -->
<!--  Last Published: Mon Feb 19 2024 10:13:00 GMT+0000 (Coordinated Universal Time)  -->
<html data-wf-page="65d32744ecac5261e14fd05b" data-wf-site="65d32744ecac5261e14fd055">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    
    <!-- Webflow CSS -->
    <link href="css/normalize.css" rel="stylesheet" type="text/css">
    <link href="css/webflow.css" rel="stylesheet" type="text/css">
    <link href="css/youni-navbar.webflow.css" rel="stylesheet" type="text/css">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js" type="text/javascript"></script>
    <script type="text/javascript">WebFont.load({  google: {    families: ["Poppins:100,200,300,regular,500,600,700,800,900"]  }});</script>
    <script type="text/javascript">!function(o,c){var n=c.documentElement,t=" w-mod-";n.className+=t+"js",("ontouchstart"in o||o.DocumentTouch&&c instanceof DocumentTouch)&&(n.className+=t+"touch")}(window,document);</script>
    <script src="https://kit.fontawesome.com/e081130a1b.js" crossorigin="anonymous"></script>

    <title>Consultant Calendar - <?php echo $consultantName ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">

    <style>
        :root {
            --primary-color: #4f235f;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }

        a:hover {
            text-decoration: none;
        }

        ::after {
            display: none !important;
        }

        .main-container {
            margin-top: 30px;
            margin-bottom: 30px;
        }

        .calendar-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        .calendar-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .controls-panel {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .controls-panel .row {
            margin: 0 -15px;
        }

        .controls-panel .col-md-3 {
            padding: 0 15px;
            margin-bottom: 20px;
        }

        .controls-panel .btn {
            margin-bottom: 10px;
        }

        .btn {
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #0f8579 0%, #32d46b 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #e6395a 0%, #e6392b 100%);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #ee82f0 0%, #f3455a 100%);
            color: white;
        }

        .btn-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #3d8bfe 0%, #00d4fe 100%);
            color: white;
        }

        .legend {
            display: flex;
            gap: 20px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }

        .busy-slot { background-color: #dc3545; }
        .free-slot { background-color: #28a745; }
        .meeting-request { background-color: #ffc107; }
        .confirmed-meeting { background-color: #17a2b8; }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
        }

        .form-group label {
            font-weight: 600;
            color: var(--dark-color);
        }

        /* FullCalendar Custom Styling */
        .fc {
            font-family: 'Poppins', sans-serif;
        }

        .fc-toolbar {
            margin-bottom: 2rem !important;
        }

        .fc-toolbar-title {
            font-size: 1.8rem !important;
            font-weight: 700 !important;
            color: var(--primary-color) !important;
        }

        .fc-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 8px 16px !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
        }

        .fc-button:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(0,0,0,0.15) !important;
        }

        .fc-button:focus {
            box-shadow: 0 0 0 3px rgba(79, 35, 95, 0.1) !important;
        }

        .fc-button-primary:not(:disabled):active {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%) !important;
        }

        .fc-daygrid-day {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            border: 1px solid #e9ecef !important;
            transition: all 0.3s ease !important;
        }

        .fc-daygrid-day:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            transform: scale(1.02) !important;
        }

        /* Remove hover effect for weekends */
        .fc-day-sat:hover,
        .fc-day-sun:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            transform: none !important;
        }

        .fc-daygrid-day-number {
            color: var(--dark-color) !important;
            font-weight: 600 !important;
            padding: 8px !important;
        }

        .fc-day-today {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
        }

        .fc-day-today .fc-daygrid-day-number {
            color: var(--primary-color) !important;
            font-weight: 700 !important;
        }

        .fc-timegrid-slot {
            border-color: #e9ecef !important;
        }

        .fc-timegrid-slot-label {
            color: var(--secondary-color) !important;
            font-weight: 500 !important;
        }

        .fc-timegrid-axis {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        }

        .fc-timegrid-col {
            border-color: #e9ecef !important;
        }

        .fc-timegrid-event {
            border-radius: 8px !important;
            border: none !important;
            font-size: 12px !important;
            padding: 4px 8px !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
            transition: all 0.3s ease !important;
        }

        .fc-timegrid-event:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }

        .fc-event {
            border-radius: 8px !important;
            border: none !important;
            font-size: 12px !important;
            padding: 4px 8px !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
            transition: all 0.3s ease !important;
        }

        .fc-event:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }

        .fc-event.busy-slot {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%) !important;
            color: white !important;
        }

        .fc-event.free-slot {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important;
            color: white !important;
        }

        .fc-event.meeting-request {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
            color: white !important;
        }

        .fc-event.confirmed-meeting {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
            color: white !important;
        }

        .fc-daygrid-event {
            border-radius: 6px !important;
            margin: 1px 0 !important;
        }

        .fc-daygrid-event:hover {
            transform: scale(1.05) !important;
        }

        .fc-scrollgrid {
            border-radius: 12px !important;
            overflow: hidden !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
        }

        .fc-scrollgrid-sync-table {
            border-radius: 12px !important;
        }

        .fc-col-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #6a4190 100%) !important;
            color: white !important;
            font-weight: 700 !important;
            padding: 12px 8px !important;
        }

        .fc-col-header-cell {
            border-color: rgba(255,255,255,0.2) !important;
        }

        /* Ensure all day labels in header are white */
        .fc-col-header-cell,
        .fc-col-header-cell a {
            color: white !important;
        }

        /* Specifically target weekend day labels in header */
        .fc-col-header .fc-day-sat,
        .fc-col-header .fc-day-sun,
        .fc-col-header .fc-day-sat a,
        .fc-col-header .fc-day-sun a {
            color: white !important;
            background: transparent !important;
        }

        .fc-timegrid-slot-minor {
            border-color: #f8f9fa !important;
        }

        .fc-timegrid-slot-label {
            font-size: 11px !important;
        }

        .fc-timegrid-event-harness {
            margin: 1px !important;
        }

        /* Weekend styling - same as weekdays */
        .fc-day-sat,
        .fc-day-sun {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
        }

        .fc-day-sat .fc-daygrid-day-number,
        .fc-day-sun .fc-daygrid-day-number {
            color: var(--dark-color) !important;
        }

        /* Business hours highlight */
        .fc-timegrid-slot.fc-timegrid-business-hours {
            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%) !important;
        }

        /* Now indicator */
        .fc-timegrid-now-indicator-line {
            border-color: var(--primary-color) !important;
            border-width: 2px !important;
        }

        /* Event selection */
        .fc-event-selected {
            box-shadow: 0 0 0 3px rgba(79, 35, 95, 0.3) !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .fc-toolbar-title {
                font-size: 1.4rem !important;
            }
            
            .fc-button {
                padding: 6px 12px !important;
                font-size: 12px !important;
            }
            
            .fc-event {
                font-size: 10px !important;
                padding: 2px 4px !important;
            }
        }

        .status-badge {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 12px;
        }

        .meeting-requests-panel {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .request-item {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .request-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        .request-item.pending {
            border-left: 5px solid #ffc107;
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        }

        .request-item.accepted {
            border-left: 5px solid #28a745;
            background: linear-gradient(135deg, #d4edda 0%, #a8e6cf 100%);
        }

        .request-item.rejected {
            border-left: 5px solid #dc3545;
            background: linear-gradient(135deg, #f8d7da 0%, #ffb3ba 100%);
        }

        .search-container {
            position: relative;
            margin-bottom: 15px;
        }

        .search-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 35, 95, 0.1);
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .student-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background: white;
        }

        .student-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .student-item:hover {
            background: #f8f9fa;
        }

        .student-item:last-child {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .legend {
                flex-direction: column;
                gap: 10px;
            }
            
            .controls-panel .row {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Include Navbar -->
    <?php include 'navbar.php'; ?>
    <br>
    <br>
    <br>

    <div class="container main-container">
        <!-- Header -->
        <div class="calendar-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-0">
                        <i class="fas fa-calendar-alt me-2" style="color: white;"></i>
                        My Calendar
                    </h2>
                    <!-- <p class="text-muted mb-0">Welcome, <?php //echo $consultantName; ?></p> -->
                </div>
                <div class="col-md-6 text-right">
                    <div class="legend">
                        <div class="legend-item">
                            <div class="legend-color busy-slot"></div>
                            <span>Busy Hours</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color free-slot"></div>
                            <span>Available</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color meeting-request"></div>
                            <span>Pending Request</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color confirmed-meeting"></div>
                            <span>Confirmed Meeting</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controls Panel -->
        <div class="controls-panel">
            <div class="row">
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#addBusyHoursModal">
                        <i class="fas fa-plus me-1"></i>Add Busy Hours
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-success btn-block" data-toggle="modal" data-target="#addRecurringModal">
                        <i class="fas fa-repeat me-1"></i>Recurring Schedule
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-info btn-block" data-toggle="modal" data-target="#createMeetingModal">
                        <i class="fas fa-video me-1"></i>Create Meeting
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-warning btn-block" onclick="refreshCalendar()">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Meeting Requests Panel -->
        <div class="meeting-requests-panel">
            <h5 class="mb-3">
                <i class="fas fa-clock me-2"></i>Pending Meeting Requests
                <span class="badge badge-warning ml-2" id="pendingCount">0</span>
            </h5>
            <div id="meetingRequestsList">
                <!-- Meeting requests will be loaded here -->
                <div class="text-center text-muted py-3">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p>No pending meeting requests</p>
                </div>
            </div>
        </div>

        <!-- Calendar Container -->
        <div class="calendar-container">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Add Busy Hours Modal -->
    <div class="modal fade" id="addBusyHoursModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-clock me-2"></i>Add Busy Hours
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addBusyHoursForm">
                        <div class="form-group">
                            <label for="busyDate">Date</label>
                            <input type="date" class="form-control" id="busyDate" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="busyStartTime">Start Time</label>
                                    <input type="time" class="form-control" id="busyStartTime" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="busyEndTime">End Time</label>
                                    <input type="time" class="form-control" id="busyEndTime" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="busyReason">Reason (Optional)</label>
                            <input type="text" class="form-control" id="busyReason" placeholder="e.g., Personal appointment, Training">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addBusyHours()">
                        <i class="fas fa-save me-1"></i>Add Busy Hours
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Recurring Schedule Modal -->
    <div class="modal fade" id="addRecurringModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-repeat me-2"></i>Add Recurring Schedule
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addRecurringForm">
                        <div class="form-group">
                            <label>Days of Week</label>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="monday" value="1">
                                        <label class="form-check-label" for="monday">Mon</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="tuesday" value="2">
                                        <label class="form-check-label" for="tuesday">Tue</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="wednesday" value="3">
                                        <label class="form-check-label" for="wednesday">Wed</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="thursday" value="4">
                                        <label class="form-check-label" for="thursday">Thu</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="friday" value="5">
                                        <label class="form-check-label" for="friday">Fri</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="saturday" value="6">
                                        <label class="form-check-label" for="saturday">Sat</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="sunday" value="0">
                                        <label class="form-check-label" for="sunday">Sun</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="recurringStartTime">Start Time</label>
                                    <input type="time" class="form-control" id="recurringStartTime" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="recurringEndTime">End Time</label>
                                    <input type="time" class="form-control" id="recurringEndTime" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="recurringType">Type</label>
                            <select class="form-control" id="recurringType" required>
                                <option value="busy">Busy Hours</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="recurringReason">Reason (Optional)</label>
                            <input type="text" class="form-control" id="recurringReason" placeholder="e.g., Regular office hours, Lunch break">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addRecurringSchedule()">
                        <i class="fas fa-save me-1"></i>Add Recurring Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Meeting Modal -->
    <div class="modal fade" id="createMeetingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-video me-2"></i>Create Meeting
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="createMeetingForm">
                        <div class="form-group">
                            <label for="meetingStudent">Select Student</label>
                            <div class="search-container">
                                <input type="text" class="search-input" id="studentSearch" placeholder="Search for a student..." autocomplete="off">
                                <i class="fas fa-search search-icon"></i>
                            </div>
                            <div class="student-list" id="studentList" style="display: none;">
                                <!-- Students will be loaded here -->
                            </div>
                            <input type="hidden" id="meetingStudent" required>
                            <div id="selectedStudent" class="mt-2" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-user me-2"></i>
                                    <span id="selectedStudentName"></span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary float-right" onclick="clearStudentSelection()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meetingDate">Date</label>
                            <input type="date" class="form-control" id="meetingDate" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="meetingStartTime">Start Time</label>
                                    <input type="time" class="form-control" id="meetingStartTime" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="meetingEndTime">End Time</label>
                                    <input type="time" class="form-control" id="meetingEndTime" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meetingTopic">Meeting Topic</label>
                            <input type="text" class="form-control" id="meetingTopic" placeholder="e.g., Application Review, University Selection">
                        </div>
                        <div class="form-group">
                            <label for="meetingNotes">Notes (Optional)</label>
                            <textarea class="form-control" id="meetingNotes" rows="3" placeholder="Additional notes for the meeting..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="createMeeting()">
                        <i class="fas fa-video me-1"></i>Create Meeting
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Meeting Request Details Modal -->
    <div class="modal fade" id="meetingRequestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-clock me-2"></i>Meeting Request Details
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="meetingRequestDetails">
                    <!-- Meeting request details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" onclick="rejectMeetingRequest()">
                        <i class="fas fa-times me-1"></i>Reject
                    </button>
                    <button type="button" class="btn btn-success" onclick="acceptMeetingRequest()">
                        <i class="fas fa-check me-1"></i>Accept
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Webflow JS (includes jQuery) -->
    <script src="https://d3e54v103j8qbb.cloudfront.net/js/jquery-3.5.1.min.dc5e7f18c8.js?site=65d32744ecac5261e14fd055" type="text/javascript" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="js/webflow.js" type="text/javascript"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

    <script>
        let calendar;
        let currentMeetingRequestId = null;
        let allStudents = [];

        // Wait for all scripts to load
        $(document).ready(function() {
            console.log('jQuery ready, initializing calendar...');
            initializeCalendar();
            loadStudents();
            loadMeetingRequests();
            initializeStudentSearch();
            
            // Test function accessibility
            window.testCreateMeeting = function() {
                console.log('Test function called - createMeeting is accessible');
                alert('Test function works!');
            };
        });

        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');
            
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                height: 'auto',
                timeZone: 'Europe/Bucharest',
                slotMinTime: '08:00:00',
                slotMaxTime: '20:00:00',
                slotDuration: '00:30:00',
                snapDuration: '00:15:00',
                allDaySlot: false,
                nowIndicator: true,
                selectable: true,
                selectMirror: true,
                editable: true,
                dayMaxEvents: true,
                weekends: true,
                firstDay: 1, // Start week on Monday
                businessHours: {
                    daysOfWeek: [1, 2, 3, 4, 5], // Monday - Friday
                    startTime: '09:00',
                    endTime: '17:00',
                },
                select: function(arg) {
                    // Handle date/time selection for adding busy hours
                    const startDate = arg.start;
                    const endDate = arg.end;
                    
                    // Set the form values
                    document.getElementById('busyDate').value = startDate.toISOString().split('T')[0];
                    document.getElementById('busyStartTime').value = startDate.toTimeString().slice(0, 5);
                    document.getElementById('busyEndTime').value = endDate.toTimeString().slice(0, 5);
                    
                    // Show the modal
                    $('#addBusyHoursModal').modal('show');
                },
                eventClick: function(arg) {
                    // Handle event clicks
                    const event = arg.event;
                    
                    if (event.extendedProps.type === 'meeting_request') {
                        showMeetingRequestDetails(event.id);
                    } else if (event.extendedProps.type === 'busy_hours') {
                        showBusyHoursDetails(event.id);
                    }
                },
                events: function(info, successCallback, failureCallback) {
                    // Load events from server
                    loadCalendarEvents(info.start, info.end, successCallback, failureCallback);
                }
            });

            calendar.render();
        }

        function loadCalendarEvents(start, end, successCallback, failureCallback) {
            // This will be implemented when we add backend functionality
            // For now, we'll show some sample events
            const events = [
                {
                    id: '1',
                    title: 'Busy Hours',
                    start: '2024-01-15T10:00:00',
                    end: '2024-01-15T12:00:00',
                    backgroundColor: '#dc3545',
                    borderColor: '#dc3545',
                    extendedProps: {
                        type: 'busy_hours',
                        reason: 'Personal appointment'
                    }
                },
                {
                    id: '2',
                    title: 'Meeting Request - John Doe',
                    start: '2024-01-16T14:00:00',
                    end: '2024-01-16T15:00:00',
                    backgroundColor: '#ffc107',
                    borderColor: '#ffc107',
                    textColor: '#000',
                    extendedProps: {
                        type: 'meeting_request',
                        studentName: 'John Doe',
                        studentEmail: 'john@example.com',
                        topic: 'Application Review'
                    }
                }
            ];
            
            successCallback(events);
        }

        function loadStudents() {
            // This will load students from the database
            // For now, we'll add some sample students
            allStudents = [
                { id: 1, name: 'John Doe', email: 'john@example.com' },
                { id: 2, name: 'Jane Smith', email: 'jane@example.com' },
                { id: 3, name: 'Mike Johnson', email: 'mike@example.com' },
                { id: 4, name: 'Sarah Wilson', email: 'sarah@example.com' },
                { id: 5, name: 'David Brown', email: 'david@example.com' },
                { id: 6, name: 'Emily Davis', email: 'emily@example.com' },
                { id: 7, name: 'Michael Garcia', email: 'michael@example.com' },
                { id: 8, name: 'Lisa Martinez', email: 'lisa@example.com' }
            ];
        }

        function loadMeetingRequests() {
            // This will load pending meeting requests from the database
            // For now, we'll show some sample requests
            const requestsList = document.getElementById('meetingRequestsList');
            const pendingCount = document.getElementById('pendingCount');
            
            const sampleRequests = [
                {
                    id: 1,
                    studentName: 'John Doe',
                    studentEmail: 'john@example.com',
                    date: '2024-01-16',
                    startTime: '14:00',
                    endTime: '15:00',
                    topic: 'Application Review',
                    status: 'pending'
                },
                {
                    id: 2,
                    studentName: 'Jane Smith',
                    studentEmail: 'jane@example.com',
                    date: '2024-01-17',
                    startTime: '10:00',
                    endTime: '11:00',
                    topic: 'University Selection',
                    status: 'pending'
                }
            ];

            if (sampleRequests.length === 0) {
                requestsList.innerHTML = `
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No pending meeting requests</p>
                    </div>
                `;
                pendingCount.textContent = '0';
            } else {
                let html = '';
                sampleRequests.forEach(request => {
                    html += `
                        <div class="request-item ${request.status}" onclick="showMeetingRequestDetails(${request.id})">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1">${request.studentName}</h6>
                                    <p class="mb-1 text-muted">${request.topic}</p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        ${request.date} at ${request.startTime} - ${request.endTime}
                                    </small>
                                </div>
                                <div class="col-md-4 text-right">
                                    <span class="status-badge badge badge-warning">Pending</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                requestsList.innerHTML = html;
                pendingCount.textContent = sampleRequests.length;
            }
        }

        function addBusyHours() {
            const form = document.getElementById('addBusyHoursForm');
            const formData = new FormData(form);
            
            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // This will be implemented when we add backend functionality
            console.log('Adding busy hours:', {
                date: document.getElementById('busyDate').value,
                startTime: document.getElementById('busyStartTime').value,
                endTime: document.getElementById('busyEndTime').value,
                reason: document.getElementById('busyReason').value
            });

            // Show success message
            alert('Busy hours added successfully!');
            $('#addBusyHoursModal').modal('hide');
            form.reset();
            calendar.refetchEvents();
        }

        function addRecurringSchedule() {
            const form = document.getElementById('addRecurringForm');
            
            // Get selected days
            const selectedDays = [];
            const dayCheckboxes = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            dayCheckboxes.forEach(day => {
                if (document.getElementById(day).checked) {
                    selectedDays.push(parseInt(document.getElementById(day).value));
                }
            });

            if (selectedDays.length === 0) {
                alert('Please select at least one day of the week.');
                return;
            }

            // This will be implemented when we add backend functionality
            console.log('Adding recurring schedule:', {
                days: selectedDays,
                startTime: document.getElementById('recurringStartTime').value,
                endTime: document.getElementById('recurringEndTime').value,
                type: document.getElementById('recurringType').value,
                reason: document.getElementById('recurringReason').value
            });

            // Show success message
            alert('Recurring schedule added successfully!');
            $('#addRecurringModal').modal('hide');
            form.reset();
            calendar.refetchEvents();
        }

        // Make createMeeting globally accessible
        window.createMeeting = function() {
            console.log('createMeeting function called');
            
            const form = document.getElementById('createMeetingForm');
            const studentId = document.getElementById('meetingStudent').value;
            const date = document.getElementById('meetingDate').value;
            const startTime = document.getElementById('meetingStartTime').value;
            const endTime = document.getElementById('meetingEndTime').value;
            const topic = document.getElementById('meetingTopic').value;
            const notes = document.getElementById('meetingNotes').value;
            
            console.log('Form values:', {
                studentId: studentId,
                date: date,
                startTime: startTime,
                endTime: endTime,
                topic: topic,
                notes: notes
            });
            
            // Validate required fields
            if (!studentId || studentId === '') {
                alert('Please select a student.');
                document.getElementById('studentSearch').focus();
                return false;
            }
            
            if (!date || date === '') {
                alert('Please select a date.');
                document.getElementById('meetingDate').focus();
                return false;
            }
            
            if (!startTime || startTime === '') {
                alert('Please select a start time.');
                document.getElementById('meetingStartTime').focus();
                return false;
            }
            
            if (!endTime || endTime === '') {
                alert('Please select an end time.');
                document.getElementById('meetingEndTime').focus();
                return false;
            }
            
            if (!topic || topic.trim() === '') {
                alert('Please enter a meeting topic.');
                document.getElementById('meetingTopic').focus();
                return false;
            }
            
            // Validate time logic
            if (startTime >= endTime) {
                alert('End time must be after start time.');
                document.getElementById('meetingEndTime').focus();
                return false;
            }

            // Validate date is not in the past
            const selectedDate = new Date(date);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                alert('Meeting date cannot be in the past.');
                document.getElementById('meetingDate').focus();
                return false;
            }

            // This will be implemented when we add backend functionality
            console.log('Creating meeting with valid data:', {
                studentId: studentId,
                date: date,
                startTime: startTime,
                endTime: endTime,
                topic: topic,
                notes: notes
            });

            // Show success message
            alert('Meeting created successfully!');
            
            // Close modal
            $('#createMeetingModal').modal('hide');
            
            // Reset form
            form.reset();
            clearStudentSelection();
            
            // Refresh calendar
            calendar.refetchEvents();
            
            return true;
        }

        function showMeetingRequestDetails(requestId) {
            currentMeetingRequestId = requestId;
            
            // This will load the actual request details from the database
            const requestDetails = `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Student:</strong><br>
                        John Doe<br>
                        <small class="text-muted">john@example.com</small>
                    </div>
                    <div class="col-md-6">
                        <strong>Date & Time:</strong><br>
                        January 16, 2024<br>
                        <small class="text-muted">2:00 PM - 3:00 PM</small>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <strong>Topic:</strong><br>
                        Application Review
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <strong>Message:</strong><br>
                        <p class="text-muted">Hi, I would like to schedule a meeting to review my university applications. I have some questions about the requirements and deadlines.</p>
                    </div>
                </div>
            `;
            
            document.getElementById('meetingRequestDetails').innerHTML = requestDetails;
            $('#meetingRequestModal').modal('show');
        }

        function acceptMeetingRequest() {
            if (!currentMeetingRequestId) return;

            // This will be implemented when we add backend functionality
            console.log('Accepting meeting request:', currentMeetingRequestId);
            
            alert('Meeting request accepted!');
            $('#meetingRequestModal').modal('hide');
            loadMeetingRequests();
            calendar.refetchEvents();
        }

        function rejectMeetingRequest() {
            if (!currentMeetingRequestId) return;

            // This will be implemented when we add backend functionality
            console.log('Rejecting meeting request:', currentMeetingRequestId);
            
            alert('Meeting request rejected.');
            $('#meetingRequestModal').modal('hide');
            loadMeetingRequests();
            calendar.refetchEvents();
        }

        function showBusyHoursDetails(eventId) {
            // This will show details of busy hours event
            alert('Busy hours details for event: ' + eventId);
        }

        function refreshCalendar() {
            calendar.refetchEvents();
            loadMeetingRequests();
        }

        function initializeStudentSearch() {
            const searchInput = document.getElementById('studentSearch');
            const studentList = document.getElementById('studentList');

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                
                if (query.length < 2) {
                    studentList.style.display = 'none';
                    return;
                }

                const filteredStudents = allStudents.filter(student => 
                    student.name.toLowerCase().includes(query) || 
                    student.email.toLowerCase().includes(query)
                );

                if (filteredStudents.length > 0) {
                    displayStudents(filteredStudents);
                    studentList.style.display = 'block';
                } else {
                    studentList.innerHTML = '<div class="student-item text-muted">No students found</div>';
                    studentList.style.display = 'block';
                }
            });

            // Hide student list when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.search-container')) {
                    studentList.style.display = 'none';
                }
            });
        }

        function displayStudents(students) {
            const studentList = document.getElementById('studentList');
            studentList.innerHTML = '';

            students.forEach(student => {
                const studentItem = document.createElement('div');
                studentItem.className = 'student-item';
                studentItem.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${student.name}</strong><br>
                            <small class="text-muted">${student.email}</small>
                        </div>
                    </div>
                `;
                
                studentItem.addEventListener('click', function() {
                    selectStudent(student);
                });
                
                studentList.appendChild(studentItem);
            });
        }

        function selectStudent(student) {
            document.getElementById('meetingStudent').value = student.id;
            document.getElementById('selectedStudentName').textContent = `${student.name} (${student.email})`;
            document.getElementById('selectedStudent').style.display = 'block';
            document.getElementById('studentSearch').value = '';
            document.getElementById('studentList').style.display = 'none';
        }

        function clearStudentSelection() {
            document.getElementById('meetingStudent').value = '';
            document.getElementById('selectedStudent').style.display = 'none';
            document.getElementById('studentSearch').value = '';
        }

        // Reset form when modal is closed
        $('#createMeetingModal').on('hidden.bs.modal', function () {
            const form = document.getElementById('createMeetingForm');
            form.reset();
            clearStudentSelection();
        });

        // Reset form when modal is closed
        $('#addBusyHoursModal').on('hidden.bs.modal', function () {
            const form = document.getElementById('addBusyHoursForm');
            form.reset();
        });

        // Reset form when modal is closed
        $('#addRecurringModal').on('hidden.bs.modal', function () {
            const form = document.getElementById('addRecurringForm');
            form.reset();
        });
    </script>
</body>
</html>
