<?php
// Set the NSE API URL
$nse_url = "https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%20500";

// Initialize cURL session
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $nse_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36",
    "Referer: https://www.nseindia.com",
]);

// Execute the request
$response = curl_exec($ch);
curl_close($ch);

// Check if the response is valid
if ($response === false) {
    echo json_encode(["error" => "Could not fetch data"]);
    exit;
}

// Return JSON response
header("Content-Type: application/json");
echo $response;
