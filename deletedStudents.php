<?php
    session_start();
    require_once "configDatabase.php";


    if (!isset($_SESSION['type'])) {
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $userId = $_SESSION["id"];
    }

    if ($typeAccount != 1) {
        header("location:index.php");
        die();
    }

?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Design System CSS -->
    <link rel="stylesheet" href="student/design-system.css">
    <title>Deleted Students List - Youni</title>

    <style>
        /* Deleted Students List Page Design System Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        #content {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--spacing-2xl);
            min-height: calc(100vh - 120px);
        }

        #contentStudents {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--light-gray);
            backdrop-filter: blur(10px);
        }

        .navbar {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--light-gray);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-2xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 2px solid var(--light-gray);
        }

        h1 {
            color: var(--primary-color);
            font-weight: var(--font-weight-bold);
            font-size: var(--font-size-4xl);
            margin: 0;
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        h1::before {
            content: '\f2ed';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-2xl);
            color: var(--danger-color);
        }

        .search-container {
            margin-bottom: var(--spacing-2xl);
            position: relative;
        }

        .search-bar {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg) var(--spacing-md) var(--spacing-2xl);
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition-normal);
            background: var(--white);
        }

        .search-bar:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
        }

        .search-icon {
            position: absolute;
            left: var(--spacing-md);
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            font-size: var(--font-size-lg);
            pointer-events: none;
        }

        .search-count {
            color: var(--primary-color);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-lg);
            margin-bottom: var(--spacing-xl);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm) var(--spacing-lg);
            background: var(--light-bg);
            border-radius: var(--border-radius-lg);
            border: 1px solid var(--light-gray);
        }

        .search-count::before {
            content: '\f2ed';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-base);
            color: var(--danger-color);
        }

        .list-group-item {
            margin-bottom: var(--spacing-lg);
            border-radius: var(--border-radius-lg) !important;
            border: 1px solid var(--light-gray);
            transition: var(--transition-normal);
            padding: var(--spacing-xl);
            background: var(--white);
            box-shadow: var(--shadow-sm);
        }

        .list-group-item:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
            border-color: var(--primary-color);
        }

        .full-name {
            font-weight: var(--font-weight-semibold);
            color: var(--primary-color);
            font-size: var(--font-size-xl);
            margin-bottom: var(--spacing-md);
            line-height: 1.4;
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .full-name::before {
            content: '\f007';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-lg);
            color: var(--danger-color);
        }

        .student-info {
            color: var(--secondary-color);
            font-size: var(--font-size-base);
            margin-bottom: var(--spacing-sm);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .student-info::before {
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-sm);
            color: var(--primary-color);
        }

        .highSchool::before { content: '\f19c'; }
        .email::before { content: '\f0e0'; }
        .consultant::before { content: '\f007'; }
        .grade::before { content: '\f3d1'; }
        .package::before { content: '\f1b3'; }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: var(--spacing-sm) var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-semibold);
            transition: var(--transition-normal);
            color: var(--white);
            font-family: 'Poppins', sans-serif;
            font-size: var(--font-size-sm);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            opacity: 0.9;
        }

        .btn-primary::after {
            content: '\f061';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-sm);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: var(--border-radius-sm);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: var(--border-radius-sm);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #content {
                padding: var(--spacing-md);
            }

            h1 {
                font-size: var(--font-size-2xl);
            }

            #contentStudents {
                padding: var(--spacing-lg);
            }
        }
    </style>
    
  </head>

  
  <?php include("navbar.php"); ?>

  <div id="content">
    <div id="contentStudents">
        <div class="page-header">
            <h1>Deleted Students List</h1>
        </div>

        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="search-bar" class="search-bar" onkeyup="searchFunction()" placeholder="Search for deleted students..." title="Type in a name">
        </div>

        <p>Found <span class="search-count"><?php echo $noStudents; ?></span> deleted students</p>

        <ol id="students-list" class="list-group list-group-numbered">
            <?php
            
            $sqlStudent = "SELECT * FROM studentData WHERE `activityStatus` = 2";
            $queryStudent = mysqli_query($link, $sqlStudent);

            $noStudents = mysqli_num_rows($queryStudent);

            if (!isset($noStudents))
                $noStudents = 0;

            while ($row = mysqli_fetch_assoc($queryStudent)) {
              ?>
                <div class="student">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="full-name"><?php echo htmlspecialchars($row['name']); ?></div>
                            
                            <p class="student-info highSchool">
                                <strong>High School:</strong> <?php echo htmlspecialchars($row['highSchool']); ?>
                            </p>
                            
                            <p class="student-info email">
                                <strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?>
                            </p>
                            
                            <p class="student-info consultant">
                                <strong>Consultant:</strong> <?php echo htmlspecialchars($row['consultantName']); ?>
                            </p>
                            
                            <?php if ($row['grade'] <= 12) { ?>
                                <p class="student-info grade">
                                    <strong>Grade:</strong> <?php echo htmlspecialchars($row['grade']); ?>
                                </p>
                            <?php } else { ?>
                                <p class="student-info grade">
                                    <strong>Grade:</strong> Bachelor
                                </p>
                            <?php } ?>

                            <?php if ($row['grade'] <= 12) { ?>
                                <p class="student-info grade">
                                    <strong>Start Grade:</strong> <?php echo htmlspecialchars($row['signGrade']); ?>
                                </p>
                            <?php } else { ?>
                                <p class="student-info grade">
                                    <strong>Start Grade:</strong> Bachelor
                                </p>
                            <?php } ?>

                            <p class="student-info package">
                                <strong>Package Type:</strong> <?php echo htmlspecialchars($row['packageType']); ?>
                            </p>
                        </div>
                        
                        <?php 
                        $urlStudent = "student.php?studentId=" . $row['studentId'];
                        ?>
                        <a href="<?php echo $urlStudent; ?>">
                            <button type="button" class="btn btn-primary">View Details</button>
                        </a>
                    </li>
                </div>
              <?php      
            }
            ?>

        </ol>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        function searchFunction() {
            var input, filter, ul, li, a, i, txtValue;
            input = document.getElementById("search-bar");
            filter = input.value.toUpperCase();
            list = document.getElementById("students-list");
            students = list.getElementsByClassName("student");
            countDisplay = 0;
            for (i = 0; i < students.length; i++) {
                name1 = students[i].getElementsByClassName("full-name")[0].innerHTML;
                name2 = students[i].getElementsByClassName("highSchool")[0].innerHTML;
                name3 = students[i].getElementsByClassName("consultant")[0].innerHTML;

                name = name1 + name2 + name3;
                if (name.toUpperCase().indexOf(filter) > -1) {
                    students[i].style.display = "";
                    countDisplay++;
                } else {
                    students[i].style.display = "none";
                }
            }
            document.getElementsByClassName("search-count")[0].innerHTML = countDisplay;
        }

        function submitForm() {
            document.getElementById('filters-form').submit();
        }
    </script>
</body>
</html>