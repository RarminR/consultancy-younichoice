<?php
    session_start();
    require_once "configDatabase.php";

    // Check if user is logged in and is admin
    if (!isset($_SESSION['type']) || $_SESSION['type'] != 1) {
        header("location: index.php");
        die();
    }

    $message = "";
    $messageType = "";

    // Function to generate hash (same as in addStudent.php)
    function generateStudentHashLink($email) {
        $hash = hash('sha256', $email . time() . uniqid());
        return $hash;
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['generate_hashes'])) {
            // Get students without hash and activity status 0
            $sql = "SELECT studentId, name, email FROM studentData WHERE activityStatus = 0";
            $result = mysqli_query($link, $sql);
            
            $updatedCount = 0;
            $errors = array();
            
            while ($row = mysqli_fetch_assoc($result)) {
                $studentId = $row['studentId'];
                $studentName = $row['name'];
                $studentEmail = $row['email'];
                
                // Generate hash
                $hash = generateStudentHashLink($studentEmail);
                
                // Update database
                $updateSql = "UPDATE studentData SET studentHashLink = '$hash' WHERE studentId = '$studentId'";
                if (mysqli_query($link, $updateSql)) {
                    $updatedCount++;
                } else {
                    $errors[] = "Failed to update hash for student: " . $studentName;
                }
            }
            
            if ($updatedCount > 0) {
                $message = "Successfully generated hashes for $updatedCount students.";
                $messageType = "success";
            } else {
                $message = "No students found that need hash generation.";
                $messageType = "info";
            }
            
            if (!empty($errors)) {
                $message .= " Errors: " . implode(", ", $errors);
                $messageType = "warning";
            }
        }
        elseif (isset($_POST['generate_single_hash'])) {
            // Handle single student hash generation
            $studentId = $_POST['generate_single_hash'];
            
            // Get student information
            $sql = "SELECT name, email FROM studentData WHERE studentId = '$studentId' AND activityStatus = 0";
            $result = mysqli_query($link, $sql);
            
            if (mysqli_num_rows($result) > 0) {
                $student = mysqli_fetch_assoc($result);
                $studentName = $student['name'];
                $studentEmail = $student['email'];
                
                // Generate hash
                $hash = generateStudentHashLink($studentEmail);
                
                // Update database
                $updateSql = "UPDATE studentData SET studentHashLink = '$hash' WHERE studentId = '$studentId'";
                if (mysqli_query($link, $updateSql)) {
                    $message = "Successfully generated hash for student: $studentName";
                    $messageType = "success";
                } else {
                    $message = "Failed to generate hash for student: $studentName";
                    $messageType = "danger";
                }
            } else {
                $message = "Student not found or not eligible for hash generation.";
                $messageType = "danger";
            }
        }
    }

    // Get students without hash and activity status 0 for display
    $sqlStudents = "SELECT studentId, name, email FROM studentData WHERE activityStatus = 0";
    $resultStudents = mysqli_query($link, $sqlStudents);
    $studentsWithoutHash = mysqli_num_rows($resultStudents);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Generate Student Hashes - Admin</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .student-list {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .btn-generate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        .btn-generate:disabled {
            background: #6c757d;
            transform: none;
            box-shadow: none;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table th {
            background: #f8f9fa;
            border: none;
            font-weight: 600;
        }
        
        .table td {
            border: none;
            border-bottom: 1px solid #dee2e6;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-inactive {
            background: #dc3545;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #dee2e6;
        }
    </style>
</head>

<body style="background: #f8f9fa;">
    <?php include("navbar.php"); ?>

    <div class="container">
        <div class="header text-center">
            <h1><i class="fas fa-key"></i> Generate Student Hashes</h1>
            <p class="mb-0">Generate onboarding hashes for existing students</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : ($messageType == 'warning' ? 'warning' : 'info'); ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : ($messageType == 'warning' ? 'exclamation-triangle' : 'info-circle'); ?>"></i>
                <?php echo $message; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="stats-card">
            <div class="row">
                <div class="col-md-6">
                    <h4><i class="fas fa-users"></i> Students Without Hash</h4>
                    <h2 class="text-primary"><?php echo $studentsWithoutHash; ?></h2>
                    <!-- <p class="text-muted">Students with activity status 0 that need hash generation</p> -->
                </div>
                <div class="col-md-6 text-right">
                    <form method="post" style="display: inline;">
                        <button type="submit" name="generate_hashes" class="btn btn-generate" <?php echo $studentsWithoutHash == 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-magic"></i> Generate All Hashes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="student-list">
            <h4><i class="fas fa-list"></i> Students Requiring Hash Generation</h4>
            
            <?php if ($studentsWithoutHash > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $counter = 1;
                            mysqli_data_seek($resultStudents, 0);
                            while ($student = mysqli_fetch_assoc($resultStudents)): 
                            ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                    </td>
                                    <td>
                                        <i class="fas fa-envelope text-muted"></i>
                                        <?php echo htmlspecialchars($student['email']); ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="generateSingleHash(<?php echo $student['studentId']; ?>, '<?php echo htmlspecialchars($student['name']); ?>')">
                                            <i class="fas fa-key"></i> Generate Hash
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h5>All Students Have Hashes!</h5>
                    <p>There are no students without hashes and activity status 0.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-4 text-center">
            <a href="studentsList.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Students List
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script>
        function generateSingleHash(studentId, studentName) {
            if (confirm('Generate hash for student: ' + studentName + '?')) {
                // Create a form and submit it
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = window.location.href;
                
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'generate_single_hash';
                input.value = studentId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html> 