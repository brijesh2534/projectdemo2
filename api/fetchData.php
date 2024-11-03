<?php

header('Content-Type: application/json');

function fetchExternalData() {
    $url = "https://api.coindesk.com/v1/bpi/currentprice/BTC.json"; // Example API for Bitcoin price

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

$data = fetchExternalData();
echo json_encode($data);
