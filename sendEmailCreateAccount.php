<?php 
    require_once "configDatabase.php";

    function sendStudentAccountCreationEmail($to, $studentName, $accountCreationLink) {
        $apiKey = 're_6XaDD7dc_2ZLrH3sHnrQhdnFnzPJsdiG9';
        $url = 'https://api.resend.com/emails';
    
        $subject = "Creați-vă contul - Youni Choice";
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>$subject</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4f235f; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background-color: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .btn { display: inline-block; background-color: #4f235f; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .btn:hover { background-color: #3a1a47; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Bun venit la Youni Choice!</h1>
                </div>
                <div class='content'>
                    <p>Bună, <strong>$studentName</strong>,</p>
                    <p>Vă mulțumim că ați ales să lucrați cu echipa noastră pentru viitorul dumneavoastră academic!</p>
                    <p>Pentru a începe să vă folosiți serviciile noastre, vă rugăm să vă creați un cont personalizat în platforma noastră.</p>
                    <p><strong>Contul dumneavoastră vă va permite să:</strong></p>
                    <ul>
                        <li>Accesați materialele și resursele personalizate</li>
                        <li>Comunicați direct cu consultantul dumneavoastră</li>
                        <li>Urmăriți progresul și programările</li>
                        <li>Accesați documentele și fișierele importante</li>
                    </ul>
                    <p style='text-align: center;'>
                        <a href='$accountCreationLink' class='btn'>Creați-vă contul acum</a>
                    </p>
                    <p><strong>Link direct:</strong> <a href='$accountCreationLink'>$accountCreationLink</a></p>
                    <p><em>Notă: Acest link este unic și personal. Nu îl partajați cu alții.</em></p>
                    <p>Dacă aveți întrebări sau întâmpinați probleme, nu ezitați să ne contactați la <strong>office@younichoice.com</strong>.</p>
                    <p>Vă dorim mult succes în călătoria dumneavoastră academică!</p>
                </div>
                <div class='footer'>
                    <p><strong>Echipa Youni Choice</strong></p>
                    <p>office@younichoice.com</p>
                </div>
            </div>
        </body>
        </html>
        ";
    
        $data = [
            'from' => 'Youni Choice <office@younichoice.com>',
            'to' => [$to],
            'subject' => $subject,
            'html' => $message,
        ];
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Log the response for debugging
        error_log("Account Creation Email Response: " . $response);
        error_log("Account Creation Email HTTP Code: " . $httpCode);
        if ($error) {
            error_log("Account Creation Email cURL Error: " . $error);
        }
    
        return $response;
    }

    // take ID out from the sql string

    $sql = "SELECT * FROM studentData WHERE studentPassword = '' AND studentId >= 380 AND studentId <= 383";
    $result = mysqli_query($link, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        sendStudentAccountCreationEmail($row['email'], $row['name'], $row['studentHashLink']);
    }
?>