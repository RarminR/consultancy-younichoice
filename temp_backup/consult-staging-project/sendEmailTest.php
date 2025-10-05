<?php

// Your Resend API Key
$apiKey = 're_6XaDD7dc_2ZLrH3sHnrQhdnFnzPJsdiG9';

// The Resend API endpoint
$url = 'https://api.resend.com/emails';

// Email data
$data = [
    'from' => 'Youni Choice <office@younichoice.com>',
    'to' => ['daniel.posdarascu@younichoice.com'],
    'subject' => 'Hello from Resend',
    'html' => '<p>This is a test email sent from the Resend API using PHP.</p>',
];

// Initialize cURL session
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey,
]);

// Execute cURL session and capture the response
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
} else {
    // Decode the JSON response
    $responseData = json_decode($response, true);

    // Check if the email was sent successfully
    if (isset($responseData['id'])) {
        echo 'Email sent successfully! Email ID: ' . $responseData['id'];
    } else {
        echo 'Failed to send email. Response: ' . print_r($responseData, true);
    }
}

// Close cURL session
curl_close($ch);

?>