<?php
function fetchStockData($url, $retries = 3) {
    $options = [
        "http" => [
            "header" => [
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36",
                "Referer: https://www.nseindia.com",
            ],
            "follow_location" => true,
            "timeout" => 5,
        ],
    ];

    $context = stream_context_create($options);

    for ($i = 0; $i < $retries; $i++) {
        $response = @file_get_contents($url, false, $context);
        if ($response !== false) {
            $data = json_decode($response, true);
            if (isset($data['data']) && is_array($data['data'])) {
                return $data['data'];
            }
        }
        sleep(1); // Wait a second before retrying
    }

    return null; // Return null if all retries fail
}
