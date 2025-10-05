<?php
    session_start();

    if (!isset($_SESSION['type'])) {
        header("location: index.php");
        die();
    }

    require_once "configDatabase.php";

    $typeAccount = $_SESSION['type'];
    $userId = $_SESSION['id'];

    // Active tab and sort direction
    $activeTab = isset($_GET['tab']) && $_GET['tab'] === 'none' ? 'none' : 'with';
    $sortDir = isset($_GET['sort']) && strtolower($_GET['sort']) === 'asc' ? 'ASC' : 'DESC';
    
    // Consultant filter
    $selectedConsultants = isset($_GET['consultant']) ? $_GET['consultant'] : array();
    $consultantString = "(";
    $firstElem = 0;
    $freqConsultant = array();

    foreach ($selectedConsultants as $consultant) {
        if ($firstElem > 0)
            $consultantString .= ',';
        $consultantString .= mysqli_real_escape_string($link, $consultant);
        $freqConsultant[$consultant] = 1;
        $firstElem += 1;
    }
    $consultantString .= ')';

    if ($firstElem == 0) {
        $consultantString = "(";
        $sqlConsultants = "SELECT userId FROM users WHERE type = 0";
        $resultConsultants = mysqli_query($link, $sqlConsultants);
        $firstElem = 0;
        while ($row = mysqli_fetch_assoc($resultConsultants)) {
            if ($firstElem > 0)
                $consultantString .= ",";
            $consultantString .= $row['userId'];
            $firstElem += 1;
        }
        $consultantString .= ")";
    }

    // Query: Students with meetings (latest date and count)
    $withMeetingsSql = "SELECT 
            sd.studentId,
            sd.name AS studentName,
            MAX(m.meetingDate) AS latestMeetingDate,
            COUNT(*) AS meetingsCount,
            (SELECT m2.meetingId FROM meetings m2 WHERE m2.studentId = sd.studentId ORDER BY m2.meetingDate DESC LIMIT 1) AS latestMeetingId,
            GROUP_CONCAT(CONCAT(m.meetingId, '|', m.meetingDate) ORDER BY m.meetingDate DESC SEPARATOR '||') AS allMeetings
        FROM meetings m
        INNER JOIN studentData sd ON sd.studentId = m.studentId";
    
    $whereClauses = array();
    $whereClauses[] = "sd.activityStatus = 0"; // Only active students
    if ($typeAccount != 1) {
        $whereClauses[] = "sd.consultantId = '" . mysqli_real_escape_string($link, $userId) . "'";
    } else {
        $whereClauses[] = "sd.consultantId IN " . $consultantString;
    }
    
    if (!empty($whereClauses)) {
        $withMeetingsSql .= " WHERE " . implode(" AND ", $whereClauses);
    }
    $withMeetingsSql .= " GROUP BY sd.studentId, sd.name ORDER BY latestMeetingDate $sortDir";
    $withMeetingsResult = mysqli_query($link, $withMeetingsSql);
    $withMeetingsCount = $withMeetingsResult ? mysqli_num_rows($withMeetingsResult) : 0;

    // Query: Students with no meetings
    $noMeetingsSql = "SELECT 
            sd.studentId,
            sd.name AS studentName
        FROM studentData sd
        LEFT JOIN meetings m ON m.studentId = sd.studentId
        ";
    $whereClauses = [];
    $whereClauses[] = "m.studentId IS NULL";
    $whereClauses[] = "sd.activityStatus = 0"; // Only active students
    if ($typeAccount != 1) {
        $whereClauses[] = "sd.consultantId = '" . mysqli_real_escape_string($link, $userId) . "'";
    } else {
        $whereClauses[] = "sd.consultantId IN " . $consultantString;
    }
    if (!empty($whereClauses)) {
        $noMeetingsSql .= " WHERE " . implode(" AND ", $whereClauses);
    }
    $noMeetingsSql .= " ORDER BY sd.name ASC";
    $noMeetingsResult = mysqli_query($link, $noMeetingsSql);
    $noMeetingsCount = $noMeetingsResult ? mysqli_num_rows($noMeetingsResult) : 0;
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Meetings List</title>
    <style>
        :root {
            --primary-color: #4f235f;
            --secondary-color: #6c757d;
            --accent-color: #007bff;
            --background-color: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            height: 80px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        #content {
            display: flex;
            gap: 20px;
            padding: 20px;
            max-width: 1600px;
            margin: 0 auto;
        }

        #contentFilter {
            width: 300px;
            flex-shrink: 0;
            position: sticky;
            top: 100px;
            max-height: calc(100vh - 140px);
            overflow-y: auto;
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 20px;
        }

        #contentMeetings {
            flex-grow: 1;
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 20px;
        }

        h1 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 30px;
            font-size: 2.2rem;
        }

        h4 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 500;
        }

        .toggle-container {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .btn-group .btn {
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-group .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        .sort-link {
            color: var(--accent-color);
            text-decoration: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .sort-link:hover {
            text-decoration: underline;
            color: #0056b3;
        }

        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }

        .table thead th {
            background-color: var(--primary-color);
            color: white;
            border: none;
            font-weight: 600;
            padding: 15px;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: var(--hover-shadow);
        }

        .table tbody td {
            padding: 15px;
            border-color: #e9ecef;
        }

        .table tbody a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }

        .table tbody a:hover {
            color: var(--accent-color);
            text-decoration: underline;
        }

        .muted {
            color: var(--secondary-color);
            font-style: italic;
        }

        .text-muted {
            color: var(--secondary-color) !important;
        }

        .meeting-date-link {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: normal;
            transition: all 0.3s ease;
        }

        .meeting-date-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .filter-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .filter-section h4 {
            color: var(--primary-color);
            font-size: 1.1rem;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--primary-color);
        }

        .filter-row {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 6px;
            transition: all 0.2s ease;
            background-color: white;
            border: 1px solid #e9ecef;
        }

        .filter-row:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
            box-shadow: var(--card-shadow);
        }

        .filter-row input[type="checkbox"] {
            margin-right: 12px;
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary-color);
        }

        .filter-row label {
            margin: 0;
            cursor: pointer;
            user-select: none;
            color: var(--secondary-color);
            font-size: 0.95rem;
            flex-grow: 1;
            transition: color 0.2s ease;
        }

        .filter-row:hover label {
            color: var(--primary-color);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .filter-buttons input[type="button"],
        .filter-buttons input[type="submit"] {
            flex: 1;
            text-align: center;
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .filter-buttons input[type="button"]:hover,
        .filter-buttons input[type="submit"]:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
        }

        .filter-buttons input[type="button"] {
            background-color: #e9ecef;
            color: var(--secondary-color);
        }

        .filter-buttons input[type="button"]:hover {
            background-color: #dee2e6;
        }
    </style>
  </head>

  <?php include("navbar.php"); ?>

  <br>
  <br>
  <br>
  <br>
  <br>

  <div id="content">
    <div id="contentFilter">
        <h3>Filters</h3>
        <br>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" id="filters-form">
            <!-- Preserve current tab and sort when filtering -->
            <input type="hidden" name="tab" value="<?php echo $activeTab; ?>">
            <input type="hidden" name="sort" value="<?php echo strtolower($sortDir); ?>">
            
            <?php if ($typeAccount == 1) { ?>
            <div class="filter-section">
                <h4>Consultants</h4>
                <?php 
                $sql = "SELECT userId, fullName FROM users WHERE type = 0";
                $result = mysqli_query($link, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    // Hide consultants whose name ends with '(inactive)'
                    if (preg_match('/\(inactive\)$/i', trim($row['fullName']))) {
                        continue;
                    }
                    ?>
                    <div class="filter-row">
                        <input type="checkbox" 
                               id="checkbox<?php echo $row['userId']; ?>" 
                               value="<?php echo $row['userId']; ?>" 
                               name="consultant[]" 
                               onchange="submitForm()"
                               <?php echo (isset($freqConsultant[$row['userId']]) && $freqConsultant[$row['userId']] == 1) ? 'checked' : ''; ?>>
                        <label for="checkbox<?php echo $row['userId']; ?>"><?php echo htmlspecialchars($row['fullName']); ?></label>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php } ?>
            
            <div class="filter-buttons">
                <input type="button" onclick="location.href='<?php echo $_SERVER['PHP_SELF']; ?>';" value="Reset">
                <input type="submit" value="Apply Filters">
            </div>
        </form>
    </div>
    
    <div id="contentMeetings">
        <h1>Meetings List</h1>

        <div class="toggle-container">
          <div class="btn-group" role="group" aria-label="Toggle sections">
            <a href="?tab=with&sort=<?php echo strtolower($sortDir); ?><?php echo !empty($selectedConsultants) ? '&' . http_build_query(['consultant' => $selectedConsultants]) : ''; ?>" class="btn btn-sm <?php echo $activeTab === 'with' ? 'btn-primary' : 'btn-outline-primary'; ?> toggle-btn">Students with meetings</a>
            <a href="?tab=none<?php echo !empty($selectedConsultants) ? '&' . http_build_query(['consultant' => $selectedConsultants]) : ''; ?>" class="btn btn-sm <?php echo $activeTab === 'none' ? 'btn-primary' : 'btn-outline-primary'; ?> toggle-btn">Students with no meetings</a>
          </div>
        </div>

    <div id="section-with" class="section <?php echo $activeTab === 'with' ? 'active' : ''; ?>">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
          <h4 class="mb-0">Students with meetings</h4>
          <p class="mb-0" style="color: var(--primary-color); font-weight: 400; font-size: 0.9rem;">
            Showing <span class="search-count"><?php echo $withMeetingsCount; ?></span> students
          </p>
        </div>
        <?php $nextSort = strtolower($sortDir) === 'asc' ? 'desc' : 'asc'; ?>
        <a class="sort-link" href="?tab=with&sort=<?php echo $nextSort; ?><?php echo !empty($selectedConsultants) ? '&' . http_build_query(['consultant' => $selectedConsultants]) : ''; ?>">
          Sort by latest date 
          <?php if (strtolower($sortDir) === 'desc'): ?>
            <span style="color: var(--primary-color); font-weight: bold;">↓</span> (Newest first)
          <?php else: ?>
            <span style="color: var(--primary-color); font-weight: bold;">↑</span> (Oldest first)
          <?php endif; ?>
          <small style="color: var(--secondary-color);">(Click to sort <?php echo strtoupper($nextSort); ?>)</small>
        </a>
      </div>
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead class="thead-light">
            <tr>
              <th scope="col">Student</th>
              <th scope="col">Latest meeting</th>
              <th scope="col">Other meetings</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($withMeetingsResult && mysqli_num_rows($withMeetingsResult) > 0) { ?>
              <?php while ($row = mysqli_fetch_assoc($withMeetingsResult)) { 
                    $latestDate = $row['latestMeetingDate'];
                    // Format date to EU format (dd.mm.yyyy) with time
                    $formattedDate = date('d.m.Y', strtotime($latestDate)) . ' @ ' . date('H:i', strtotime($latestDate));
                    $count = (int)$row['meetingsCount'];
                    $other = max(0, $count - 1);
                    $studentUrl = "student.php?studentId=" . $row['studentId'];
                    $meetingUrl = "meeting.php?meetingId=" . $row['latestMeetingId'];
                    
                    // Parse all meetings data
                    $allMeetings = [];
                    if (!empty($row['allMeetings'])) {
                        $meetingsArray = explode('||', $row['allMeetings']);
                        foreach ($meetingsArray as $meeting) {
                            $parts = explode('|', $meeting);
                            if (count($parts) == 2) {
                                $allMeetings[] = [
                                    'id' => $parts[0],
                                    'date' => $parts[1]
                                ];
                            }
                        }
                    }
                ?>
                <tr>
                  <td><a href="<?php echo $studentUrl; ?>"><?php echo htmlspecialchars($row['studentName']); ?></a></td>
                  <td><a href="<?php echo $meetingUrl; ?>" class="meeting-date-link"><?php echo htmlspecialchars($formattedDate); ?></a></td>
                  <td class="muted">
                    <?php if ($other > 0): ?>
                      <span class="other-meetings-toggle" onclick="toggleMeetings(<?php echo $row['studentId']; ?>)" style="cursor: pointer; color: var(--accent-color);">
                        + <?php echo $other; ?> other meetings
                      </span>
                      <div id="meetings-<?php echo $row['studentId']; ?>" class="all-meetings-list" style="display: none; margin-top: 8px;">
                        <?php foreach ($allMeetings as $index => $meeting): ?>
                          <?php if ($index > 0): // Skip the first one as it's already shown as latest ?>
                            <?php 
                              $meetingFormattedDate = date('d.m.Y', strtotime($meeting['date'])) . ' @ ' . date('H:i', strtotime($meeting['date']));
                              $meetingDetailUrl = "meeting.php?meetingId=" . $meeting['id'];
                            ?>
                            <div style="margin: 2px 0;">
                              <a href="<?php echo $meetingDetailUrl; ?>" class="meeting-date-link"><?php echo htmlspecialchars($meetingFormattedDate); ?></a>
                            </div>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      No other meetings
                    <?php endif; ?>
                  </td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr><td colspan="3" class="text-muted">No students with meetings found.</td></tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>

    <div id="section-none" class="section <?php echo $activeTab === 'none' ? 'active' : ''; ?>">
      <div class="mb-2">
        <h4 class="mb-0">Students with no meetings</h4>
        <p class="mb-0" style="color: var(--primary-color); font-weight: 600; font-size: 0.9rem;">
          Showing <span class="search-count"><?php echo $noMeetingsCount; ?></span> students
        </p>
      </div>
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead class="thead-light">
            <tr>
              <th scope="col">Student</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($noMeetingsResult && mysqli_num_rows($noMeetingsResult) > 0) { ?>
              <?php while ($row = mysqli_fetch_assoc($noMeetingsResult)) { 
                    $studentUrl = "student.php?studentId=" . $row['studentId'];
                ?>
                <tr>
                  <td><a href="<?php echo $studentUrl; ?>"><?php echo htmlspecialchars($row['studentName']); ?></a></td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr><td class="text-muted">All students have at least one meeting.</td></tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <script>
        function submitForm() {
            document.getElementById('filters-form').submit();
        }

        function toggleMeetings(studentId) {
            const meetingsDiv = document.getElementById('meetings-' + studentId);
            const toggleSpan = event.target;
            
            if (meetingsDiv.style.display === 'none') {
                meetingsDiv.style.display = 'block';
                toggleSpan.innerHTML = toggleSpan.innerHTML.replace('+', '−');
            } else {
                meetingsDiv.style.display = 'none';
                toggleSpan.innerHTML = toggleSpan.innerHTML.replace('−', '+');
            }
        }
    </script>
  </body>
</html>

