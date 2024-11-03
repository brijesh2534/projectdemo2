<?php
function fetchStockData($url, $retries = 3) {
    $startTime = microtime(true); // Start timer
    $options = [
        "http" => [
            "method" => "GET",
            "header" => implode("\r\n", [
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36",
                "Accept: application/json, text/plain, */*",
                "Referer: https://www.nseindia.com/",
                "X-Requested-With: XMLHttpRequest"
            ]),
            "ignore_errors" => true
        ]
    ];

    $context = stream_context_create($options);

    for ($i = 0; $i < $retries; $i++) {
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            error_log("Attempt " . ($i + 1) . " failed to fetch data.");
            sleep(2);
            continue;
        }

        $data = json_decode($response, true);

        if (isset($data['data']) && is_array($data['data'])) {
            $endTime = microtime(true); // End timer
            error_log("Data fetched in " . ($endTime - $startTime) . " seconds.");
            return $data['data'];
        }

        sleep(2);
    }

    return null;
}

// Define the URL for fetching data
$nse_url = "https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%2050";
$data = fetchStockData($nse_url);

if ($data === null) {
    echo json_encode(["error" => "Could not fetch stock data after multiple attempts."]);
    exit;
}

// Process and filter stock data
$stocks = array_filter($data, fn($stock) => $stock['pChange'] > 0);
usort($stocks, fn($a, $b) => $b['pChange'] <=> $a['pChange']);

// Prepare for displaying in HTML
$lastUpdated = date("Y-m-d H:i:s");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Gainers - Nifty 500 Pre-market</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container my-5">
        <h2 class="text-center mb-4">Nifty 500 Stocks - Top Gainers (Pre-market)</h2>
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
                foreach (array_slice($stocks, 0, 10) as $stock) {
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

    <!-- JavaScript for Auto-Refresh -->
    <script>
        function updateStockData() {
            $.get("2.php", function(data) {
                // Update the stock data table
                $('#stockData').html($(data).find('#stockData').html());
                // Update the last updated time
                $('#lastUpdated').text("Last Updated: " + new Date().toLocaleString());
            });
        }

        setInterval(updateStockData, 5000); // 5000ms = 5 seconds
    </script>
</body>
</html>
