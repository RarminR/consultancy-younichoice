<?php
session_start();
require_once 'configDatabase.php';
header('Content-Type: application/json');

// Simple test to see if the file is being accessed
echo json_encode([
    'status' => 'test',
    'session' => [
        'typeStudent' => isset($_SESSION['typeStudent']),
        'idStudent' => isset($_SESSION['idStudent']) ? $_SESSION['idStudent'] : null,
        'type' => isset($_SESSION['type']),
        'id' => isset($_SESSION['id']) ? $_SESSION['id'] : null
    ],
    'database' => isset($link) ? 'connected' : 'not connected',
    'request' => [
        'action' => $_GET['action'] ?? 'none',
        'studentId' => $_GET['studentId'] ?? 'none'
    ]
]); 