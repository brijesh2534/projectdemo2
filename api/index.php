<?php
// Fetch stock data from NSE API with retry functionality and cURL cookie handling
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

        // Retry after a short wait
        sleep(2);
    }

    curl_close($ch);
    unlink($cookieFile); // Clean up cookie file
    return null; // Return null if all retries fail
}

$nse_url = "https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%2050";
$data = fetchStockData($nse_url);

if ($data === null) {
    echo json_encode(["error" => "Could not fetch stock data after multiple attempts."]);
    exit;
}

// Processing data: filtering and sorting for top gainers
$stocks = array_filter($data, fn($stock) => $stock['pChange'] > 0);
usort($stocks, fn($a, $b) => $b['pChange'] <=> $a['pChange']);
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
            $.ajax({
                url: 'https://projectdemo2.vercel.app/api/index.php', // Change to your PHP file URL
                success: function(data) {
                    // Update the stock data table
                    $('#stockData').html($(data).find('#stockData').html());
                    // Update the last updated time
                    $('#lastUpdated').text("Last Updated: " + new Date().toLocaleString());
                }
            });
        }

        // Set interval for every 5 seconds
        setInterval(updateStockData, 5000); 
    </script>
</body>
</html>
