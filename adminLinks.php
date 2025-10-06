<?php

    session_start();
    require_once "configDatabase.php";

    if (!isset($_SESSION['type'])) { // testez daca userul est logat
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $accountId = $_SESSION["id"];
    }

    if ($typeAccount != 1) { // testez daca contul e de admin
        header("location: index.php");
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    
    <!-- Design System CSS -->
    <link rel="stylesheet" href="student/design-system.css">

    <title>Admin Links - Youni</title>

    <style>
        body {
            background: var(--bg-gradient-primary);
            font-family: var(--font-family-primary);
            min-height: 100vh;
        }

        #content {
            width: 90%;
            max-width: 1200px;
            margin: auto;
            padding: var(--spacing-2xl) var(--spacing-lg);
        }

        .admin-header {
            text-align: center;
            margin-bottom: var(--spacing-3xl);
        }

        .admin-title {
            color: var(--primary-color);
            font-size: var(--font-size-4xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-md);
        }

        .admin-subtitle {
            color: var(--secondary-color);
            font-size: var(--font-size-lg);
        }

        .admin-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-xl);
            margin-top: var(--spacing-2xl);
        }

        .admin-action-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-md);
            transition: var(--transition-normal);
            text-align: center;
        }

        .admin-action-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .admin-action-icon {
            font-size: var(--font-size-4xl);
            color: var(--primary-color);
            margin-bottom: var(--spacing-lg);
        }

        .admin-action-title {
            color: var(--primary-color);
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--spacing-md);
        }

        .admin-action-description {
            color: var(--secondary-color);
            font-size: var(--font-size-base);
            margin-bottom: var(--spacing-lg);
        }

        .btn-admin {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg);
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            border-radius: var(--border-radius-lg);
            transition: var(--transition-normal);
            text-decoration: none;
            display: inline-block;
        }

        .btn-admin:hover {
            transform: translateY(-1px);
            text-decoration: none;
        }

        .btn-admin-primary {
            background: var(--primary-gradient);
            color: var(--white);
        }

        .btn-admin-primary:hover {
            color: var(--white);
            box-shadow: var(--shadow-lg);
        }

        .btn-admin-danger {
            background: var(--danger-gradient);
            color: var(--white);
        }

        .btn-admin-danger:hover {
            color: var(--white);
            box-shadow: var(--shadow-lg);
        }

        .btn-admin-secondary {
            background: var(--secondary-gradient);
            color: var(--white);
        }

        .btn-admin-secondary:hover {
            color: var(--white);
            box-shadow: var(--shadow-lg);
        }

        @media (max-width: 768px) {
            #content {
                width: 95%;
                padding: var(--spacing-lg) var(--spacing-md);
            }
            
            .admin-actions {
                grid-template-columns: 1fr;
                gap: var(--spacing-lg);
            }
        }
    </style>
  </head>


  
  
  <?php include("navbar.php"); ?>

  <div id="content">
    <div class="admin-header">
      <h1 class="admin-title">Admin Dashboard</h1>
      <p class="admin-subtitle">Manage consultants, students, and system settings</p>
    </div>

    <div class="admin-actions">
      <div class="admin-action-card">
        <div class="admin-action-icon">
          <i class="fas fa-user-plus"></i>
        </div>
        <h3 class="admin-action-title">Add Consultant</h3>
        <p class="admin-action-description">Add a new consultant to the system</p>
        <a href="addConsultant.php" class="btn-admin btn-admin-primary">
          <i class="fas fa-plus"></i> Add Consultant
        </a>
      </div>

      <div class="admin-action-card">
        <div class="admin-action-icon">
          <i class="fas fa-user-minus"></i>
        </div>
        <h3 class="admin-action-title">Remove Consultant</h3>
        <p class="admin-action-description">Remove a consultant from the system</p>
        <a href="removeConsultantList.php<?php echo $studentId; ?>" class="btn-admin btn-admin-danger">
          <i class="fas fa-minus"></i> Remove Consultant
        </a>
      </div>

      <div class="admin-action-card">
        <div class="admin-action-icon">
          <i class="fas fa-edit"></i>
        </div>
        <h3 class="admin-action-title">Edit Student Info</h3>
        <p class="admin-action-description">Update student information and details</p>
        <a href="<?php echo "editStudent.php?studentId=".$studentId; ?>" class="btn-admin btn-admin-secondary">
          <i class="fas fa-pencil-alt"></i> Edit Student Info
        </a>
      </div>
    </div>
  </div>
</body>
</html>