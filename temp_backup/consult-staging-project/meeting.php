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

    if (isset($_GET['meetingId'])) // testez daca e setat un meeting
        $meetingId = $_GET['meetingId'];
    else {
        header("location: index.php");
        die();
    }

    $sqlMeetingData = "SELECT * FROM meetings WHERE `meetingId` = '$meetingId'";    
    $queryMeetingData = mysqli_query($link, $sqlMeetingData);

    if (mysqli_num_rows($queryMeetingData) > 0) // testez daca exista un meeting cu id-ul dat
        $dataMeeting = mysqli_fetch_assoc($queryMeetingData);
    else {
        header("location: index.php");
        die();
    }

    $studentId = $dataMeeting['studentId'];
    $consultantId = $dataMeeting['consultantId'];

    if (!($consultantId == $accountId || $typeAccount == 1)) { // testez daca are acces userul la acest meeting
        header("location: index.php");
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
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Meeting details</title>

    <style>
        #content {
            width: 70%;
            margin: auto;
        }
        #search-bar {
            background-image: url('/css/searchicon.png');
            background-position: 10px 12px;
            background-repeat: no-repeat;
            width: 100%;
            font-size: 16px;
            padding: 12px 20px 12px 40px;
            border: 1px solid #ddd;
            margin-bottom: 12px;
        }
        .full-name {
            font-weight: bold;
        }

        .navbar {
            height: 150px;
        }

        .badge {
            /* height: 30px; */
            font-size: 15px;
            color: white;
            background-color: var(--pink) !important;
            position: absolute;
            right: 50%;
        }
        
        .fw-bold {
            font-weight: bold;
        }

        .student-info {
            font-size: 18px;
            font-weight: bold;
        }

        .title-info {
            font-weight: bold;
            color: var(--pink);
            font-size: 20px;
        }

        .info-row {
            display: inline; /* the default for span */
        }

        .statusSelect {
            width: 100px;
            height: 25px;
        }

        /* Tasks Section Styles */
        .meeting-tasks-section {
            margin: 20px 0;
        }

        .tasks-container {
            margin-left: 20px;
        }

        .tasks-list {
            margin-top: 15px;
        }

        .task-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 12px;
            background-color: #f8f9fa;
            position: relative;
        }

        .task-item.done {
            background-color: #e8f5e8;
            border-color: #28a745;
        }

        .task-text {
            flex: 1;
            text-align: left;
            margin-right: 16px;
            word-wrap: break-word;
        }

        .task-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .task-deadline {
            color: #6c757d;
            font-size: 0.9em;
        }

        .edit-task-form {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .gap-2 {
            gap: 0.5rem;
        }

    </style>
  </head>

  
  <?php include("navbar.php"); ?>

  <div id = "content">
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>


    <p class = "student-info"> <span class = "title-info"> Meeting's host: </span> <a href="<?php echo 'consultant.php?consultantId='.$consultantId; ?>"><?php echo $consultantName;  ?> </a> </p>
    <p class = "student-info"> <span class = "title-info"> Student's Name: </span> <a href="<?php echo 'student.php?studentId='.$studentId; ?>"><?php echo $studentName; ?> </a> </p>
    <p class = "student-info"> <span class = "title-info"> Student's School: </span> <?php echo $studentSchool ?> </p>
    <p class = "student-info"> <span class = "title-info"> Meeting's date and hour: </span> <?php echo $meetingDate; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Meeting's notes: </span>  <br> <br><?php echo $meetingNotes; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Meeting's Topic: </span>  <br> <br><?php echo $meetingTopic; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Meeting's Activity List: </span>  <br> <br><?php echo $meetingActivities; ?> </p>

    <!-- Tasks Section -->
    <div class="meeting-tasks-section">
        <p class="student-info"> <span class="title-info"> Meeting Tasks: </span> </p>
        <div class="tasks-container">
            <div class="add-task-section mb-3">
                <button type="button" class="btn btn-success btn-sm" onclick="showAddTaskForm()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                    </svg> Add Task
                </button>
            </div>
            
            <div id="add-task-form" style="display: none;" class="mb-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Add New Task</h6>
                        <form id="task-form">
                            <div class="form-group">
                                <label for="taskText">Task Description:</label>
                                <textarea class="form-control" id="taskText" name="taskText" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="taskDeadline">Deadline (optional):</label>
                                <input type="date" class="form-control" id="taskDeadline" name="taskDeadline">
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">Create Task</button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="hideAddTaskForm()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div id="meeting-tasks-list" class="tasks-list">
                <!-- Tasks will be loaded here via JavaScript -->
            </div>
        </div>
    </div>

    <br>
    <a href = <?php echo "sendNotes.php?meetingId=".$meetingId; ?> > <button class = "btn btn-primary"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
    </svg> Send Notes to Student & Parent </button> </a>

    <br>
    <br>
    
    <a href = <?php echo "editMeeting.php?meetingId=".$meetingId; ?> > <button class = "btn btn-primary"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
    </svg> Edit meeting info </button> </a>
    <br>
    <br>
    <a onclick="confirmRemove('removeMeeting.php?meetingId=<?php echo $meetingId; ?>')"> <button class = "btn btn-danger"> <i class="fa-solid fa-minus"></i> Remove </button> </a>
    <br>
    <br>

    

    


    <br>
    <br>


    
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script>
        // Meeting ID for task operations
        const meetingId = <?php echo $meetingId; ?>;
        const studentId = <?php echo $studentId; ?>;
        let tasksLoaded = false;

        // Load tasks when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadMeetingTasks();
        });

        // Task management functions
        function loadMeetingTasks() {
            fetch(`tasksActions.php?action=listByMeeting&meetingId=${meetingId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayMeetingTasks(data.tasks);
                        tasksLoaded = true;
                    } else {
                        console.error('Error loading tasks:', data.error);
                        document.getElementById('meeting-tasks-list').innerHTML = '<p class="text-muted">Error loading tasks.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('meeting-tasks-list').innerHTML = '<p class="text-muted">Error loading tasks.</p>';
                });
        }

        function displayMeetingTasks(tasks) {
            const tasksList = document.getElementById('meeting-tasks-list');
            
            if (tasks.length === 0) {
                tasksList.innerHTML = '<p class="text-muted">No tasks assigned during this meeting.</p>';
                return;
            }

            const tasksHtml = tasks.map(task => {
                const deadlineText = task.taskDeadline ? 
                    `Deadline: ${formatDate(task.taskDeadline)}` : 
                    'No deadline';
                
                const statusClass = task.taskStatus === 'Done' ? 'done' : '';
                const statusButtonText = task.taskStatus === 'Done' ? 'Mark as In Progress' : 'Mark as Done';
                const statusButtonClass = task.taskStatus === 'Done' ? 'btn-warning' : 'btn-success';

                return `
                    <div class="task-item ${statusClass}" style="display: flex; align-items: center;">
                        <span class="task-text">${escapeHtml(task.taskText)}</span>
                        <div class="task-actions">
                            <small class="task-deadline">${deadlineText}</small>
                            <button class="btn btn-sm ${statusButtonClass}" onclick="toggleMeetingTask(${task.taskId})" title="${statusButtonText}">
                                ${task.taskStatus === 'Done' ? '✓' : '○'}
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="editMeetingTask(${task.taskId})" title="Edit Task">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                                </svg>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteMeetingTask(${task.taskId})" title="Delete Task">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 1-1 0v6a.5.5 0 0 1 1 0V6z"/>
                                    <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4.5 4.5V6H13V4.5L13.382 4H4.118zM2.5 7V5h11v2H2.5z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');

            tasksList.innerHTML = tasksHtml;
        }

        function showAddTaskForm() {
            document.getElementById('add-task-form').style.display = 'block';
            document.getElementById('taskText').focus();
        }

        function hideAddTaskForm() {
            document.getElementById('add-task-form').style.display = 'none';
            document.getElementById('task-form').reset();
        }

        function toggleMeetingTask(taskId) {
            const formData = new FormData();
            formData.append('taskId', taskId);

            fetch('tasksActions.php?action=toggle', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    tasksLoaded = false;
                    loadMeetingTasks();
                } else {
                    alert('Error updating task status: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating task status.');
            });
        }

        function editMeetingTask(taskId) {
            const taskItem = document.querySelector(`button[onclick*="editMeetingTask(${taskId})"]`).closest('.task-item');
            if (!taskItem) return;

            const taskTextSpan = taskItem.querySelector('.task-text');
            const deadlineText = taskItem.querySelector('.task-deadline');
            const currentText = taskTextSpan.textContent.trim();

            // Extract deadline information
            let deadlineValue = '';
            if (deadlineText && deadlineText.textContent) {
                const deadlineTextContent = deadlineText.textContent;
                if (deadlineTextContent.includes('Deadline: ') && !deadlineTextContent.includes('No deadline')) {
                    const deadlineDate = deadlineTextContent.replace('Deadline: ', '');
                    const dateParts = deadlineDate.split('/');
                    if (dateParts.length === 3) {
                        const day = parseInt(dateParts[0]);
                        const month = parseInt(dateParts[1]);
                        const year = parseInt(dateParts[2]);
                        deadlineValue = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                    }
                }
            }

            // Store original content
            const originalContent = taskItem.innerHTML;
            taskItem.setAttribute('data-original-content', originalContent);

            // Create edit form
            const editForm = document.createElement('div');
            editForm.className = 'edit-task-form';
            editForm.innerHTML = `
                <div class="form-group mb-2">
                    <textarea class="form-control" id="edit-task-text-${taskId}" rows="3" required>${escapeHtml(currentText)}</textarea>
                </div>
                <div class="form-group mb-2">
                    <label for="edit-task-deadline-${taskId}" class="form-label">Deadline (optional)</label>
                    <input type="date" class="form-control" id="edit-task-deadline-${taskId}" value="${deadlineValue}">
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm" onclick="saveMeetingTaskEdit(${taskId})">Save</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="cancelMeetingTaskEdit(${taskId})">Cancel</button>
                </div>
            `;

            taskItem.innerHTML = '';
            taskItem.appendChild(editForm);
        }

        function saveMeetingTaskEdit(taskId) {
            const taskText = document.getElementById(`edit-task-text-${taskId}`).value.trim();
            const taskDeadline = document.getElementById(`edit-task-deadline-${taskId}`).value;

            if (!taskText) {
                alert('Task description is required.');
                return;
            }

            const formData = new FormData();
            formData.append('taskId', taskId);
            formData.append('taskText', taskText);
            formData.append('taskDeadline', taskDeadline);

            fetch('tasksActions.php?action=update', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    tasksLoaded = false;
                    loadMeetingTasks();
                } else {
                    alert('Error updating task: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating task.');
            });
        }

        function cancelMeetingTaskEdit(taskId) {
            const taskItem = document.querySelector(`#edit-task-text-${taskId}`).closest('.task-item');
            if (taskItem && taskItem.hasAttribute('data-original-content')) {
                taskItem.innerHTML = taskItem.getAttribute('data-original-content');
            }
        }

        function deleteMeetingTask(taskId) {
            if (!confirm('Are you sure you want to delete this task?')) {
                return;
            }

            const formData = new FormData();
            formData.append('taskId', taskId);

            fetch('tasksActions.php?action=delete', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    tasksLoaded = false;
                    loadMeetingTasks();
                } else {
                    alert('Error deleting task: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting task.');
            });
        }

        // Utility functions
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const day = date.getDate().toString().padStart(2, '0');
            const month = (date.getMonth() + 1).toString().padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Form submission handler
        document.getElementById('task-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const taskText = document.getElementById('taskText').value.trim();
            const taskDeadline = document.getElementById('taskDeadline').value;

            if (!taskText) {
                alert('Task description is required.');
                return;
            }

            const formData = new FormData();
            formData.append('studentId', studentId);
            formData.append('taskText', taskText);
            formData.append('taskDeadline', taskDeadline);
            formData.append('meetingId', meetingId);

            fetch('tasksActions.php?action=add', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    hideAddTaskForm();
                    tasksLoaded = false;
                    loadMeetingTasks();
                } else {
                    alert('Error creating task: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating task.');
            });
        });

        function confirmRemove(link) {
            const userConfirmed = confirm("Are you sure you want to delete this meeting?");
            if (userConfirmed) {
                window.location.href = link;
            }
        }
    </script>
</body>
</html>