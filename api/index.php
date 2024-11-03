<?php
function fetchStockData($url, $retries = 3) {
    $cookieFile = tempnam(sys_get_temp_dir(), 'cookie'); // Temporary file for cookies
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile); // Store cookies
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile); // Send cookies
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36",
        "Accept: application/json, text/plain, */*",
        "Referer: https://www.nseindia.com/",
        "X-Requested-With: XMLHttpRequest",
    ]);

    for ($i = 0; $i < $retries; $i++) {
        $response = curl_exec($ch);

        if ($response === false) {
            echo "cURL Error: " . curl_error($ch) . "\n";
            break; // Exit the loop if cURL fails
        }

        $data = json_decode($response, true);
        
        // Check for expected structure
        if (isset($data['data']) && is_array($data['data'])) {
            curl_close($ch);
            unlink($cookieFile); // Clean up cookie file
            return $data['data'];
        }

        // If we don't get valid data, wait a bit and retry
        sleep(2);
    }

    curl_close($ch);
    unlink($cookieFile); // Clean up cookie file
    return null; // Return null if all retries fail
}

// get function to manage stock data fetching
function get($url) {
    $data = fetchStockData($url);

    if ($data === null) {
        return [
            "error" => "Could not fetch stock data after multiple attempts.",
            "data" => []
        ];
    }

    // Filter and sort top gainers
    $stocks = array_filter($data, fn($stock) => isset($stock['pChange']) && $stock['pChange'] > 0);
    usort($stocks, fn($a, $b) => $b['pChange'] <=> $a['pChange']);

    return [
        "error" => null,
        "data" => $stocks
    ];
}

// Usage
$nse_url = "https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%2050";
$response = get($nse_url);

if ($response['error']) {
    echo json_encode(["error" => $response['error']]);
    exit;
}

// Process and display data if available
$stocks = array_slice($response['data'], 0, 10); // Top 10 gainers
$lastUpdated = date("Y-m-d H:i:s");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Gainers - Nifty 50</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container my-5">
        <h2 class="text-center mb-4">Nifty 50 Stocks - Top Gainers</h2>
        <p class="text-center" id="lastUpdated">Last Updated: <?php echo $lastUpdated; ?></p>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Stock Symbol</th>
                    <th>Company Name</th>
                    <th>Last Price</th>
                    <th>% Change</th>
                </tr>
            </thead>
            <tbody id="stockData">
                <?php
                foreach ($stocks as $stock) {
                    $symbol = $stock['symbol'] ?? 'N/A';
                    $companyName = $stock['companyName'] ?? $symbol; // Use symbol if companyName is missing
                    $lastPrice = $stock['lastPrice'] ?? 'N/A';
                    $pChange = $stock['pChange'] ?? 'N/A';

                    echo "<tr>
                        <td>{$symbol}</td>
                        <td>{$companyName}</td>
                        <td>₹{$lastPrice}</td>
                        <td>{$pChange}%</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
