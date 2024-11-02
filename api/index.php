<?php
function fetchStockData($url, $retries = 3) {
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36",
            "Referer: https://www.nseindia.com",
        ],
    ];

    for ($i = 0; $i < $retries; $i++) {
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response !== false) {
            $data = json_decode($response, true);
            if (isset($data['data']) && is_array($data['data'])) {
                return $data['data'];
            }
        }
        sleep(1); // Wait a second before retrying
    }

    return null;
}

$nse_url = "https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%20500";
$data = fetchStockData($nse_url);

if ($data === null) {
    echo json_encode(["error" => "Could not fetch stock data after multiple attempts."]);
    exit;
}

$stocks = array_filter($data, fn($stock) => $stock['pChange'] > 0);
usort($stocks, fn($a, $b) => $b['pChange'] <=> $a['pChange']);
echo json_encode(array_slice($stocks, 0, 10)); // Return top 10 gainers
