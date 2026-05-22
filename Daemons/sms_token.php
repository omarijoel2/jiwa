<?php

function saveToken(){
    $filename = "/var/www/html/win/Daemons/token.json";

    $url = 'https://developer.taifamobile.co.ke/auth/token';
    $username = "dimacsint@gmail.com";
    $password = "FgI1xwWuBnwbAwG*";
    $data = array(
        'username' => $username,
        'password' => $password
    );

    $options = array(
        'http' => array(
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );

    $context  = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === FALSE) {
        die('Error occurred while fetching data from server');
    }

    $result = $response;


    // Open or create the file
    $file = fopen($filename, "w");

    fwrite($file, $result."\n");
    
    // Close the file
    fclose($file);

}

saveToken();
