<?php
header('Content-Type: application/json');

// Define the URL for the NSE API
$url = "https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%2050";

// Function to fetch data with retries
function fetchData($url, $retries = 3) {
    for ($i = 0; $i < $retries; $i++) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
            "Accept: application/json, text/plain, */*",
            "Referer: https://www.nseindia.com/",
            "X-Requested-With: XMLHttpRequest"
        ]);

        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            sleep(1);
            continue;
        }

        curl_close($ch);
        $jsonData = json_decode($data, true);
        if ($jsonData !== null && isset($jsonData['data'])) {
            return $jsonData; // Return decoded JSON if valid
        }

        sleep(1);
    }
    return null;
}

// Fetch data with retries
$jsonData = fetchData($url);
if ($jsonData === null) {
    echo json_encode(["error" => "Failed to fetch data from the API."]);
    exit;
}

// Sort stocks by percentage change (`pChange`) in descending order
usort($jsonData['data'], function($a, $b) {
    return $b['pChange'] <=> $a['pChange'];
});

// Get the top 10 gainers
$topGainers = array_slice($jsonData['data'], 0, 10);

// Return the data as JSON
echo json_encode($topGainers);
