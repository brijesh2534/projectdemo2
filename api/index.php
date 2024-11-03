<?php
function fetchStockData($url, $cacheFile = 'cache.json', $cacheTime = 300) {
    // Check if cached data exists and is still valid
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    // Fetch new data if cache is stale or doesn't exist
    $response = @file_get_contents($url);
    
    if ($response === false) {
        error_log("Failed to fetch data from NSE.");
        return null;
    }

    $data = json_decode($response, true);
    
    // Save the data to cache
    file_put_contents($cacheFile, json_encode($data));

    return $data;
}

$nse_url = "https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%2050";
$data = fetchStockData($nse_url);

if ($data === null || !isset($data['data'])) {
    echo json_encode(["error" => "Could not fetch stock data after multiple attempts."]);
    exit;
}

// Your logic for processing $data
// Example: filtering and sorting top gainers
$stocks = array_filter($data['data'], fn($stock) => $stock['pChange'] > 0);
usort($stocks, fn($a, $b) => $b['pChange'] <=> $a['pChange']);

// Prepare for displaying in HTML
$lastUpdated = date("Y-m-d H:i:s");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Gainers - Nifty 50 Pre-market</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container my-5">
        <h2 class="text-center mb-4">Nifty 50 Stocks - Top Gainers (Pre-market)</h2>
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
            $.get("index.php", function(data) {
                // Update the stock data table
                $('#stockData').html($(data).find('#stockData').html());
                // Update the last updated time
                $('#lastUpdated').text("Last Updated: " + new Date().toLocaleString());
            });
        }

        setInterval(updateStockData, 30000); // 30000ms = 30 seconds
    </script>
</body>
</html>
