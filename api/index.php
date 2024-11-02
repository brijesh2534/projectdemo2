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

        // Wait a second before retrying
        sleep(1);
    }

    return null; // Return null if all retries fail
}

$nse_url = "https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%20500";
$data = fetchStockData($nse_url);

if ($data === null) {
    echo "Error: Could not fetch stock data after multiple attempts.";
    exit;
}

// Filter and sort top gainers
$stocks = array_filter($data, fn($stock) => $stock['pChange'] > 0);
usort($stocks, fn($a, $b) => $b['pChange'] <=> $a['pChange']);
$lastUpdated = date("Y-m-d H:i:s"); // Store last updated time
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
            $.get("1.php", function(data) {
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
