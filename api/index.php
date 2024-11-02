<?php
function fetchStockData($url) {
    // Use file_get_contents directly to fetch data
    $response = file_get_contents($url);

    // Check if the response is false (indicating an error)
    if ($response === false) {
        return null; // Handle error gracefully
    }

    $data = json_decode($response, true);
    return $data['data'] ?? null; // Return the data or null if not found
}

// Define the NSE URL to fetch stock data
$nse_url = "https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%20500";
$data = fetchStockData($nse_url);

// Check if data was fetched successfully
if ($data === null) {
    echo "Error: Could not fetch stock data.";
    exit; // Exit if there's an error
}

// Filter top gainers
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
                    $companyName = $stock['companyName'] ?? $symbol;
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

    <script>
        function updateStockData() {
            $.get("api/index.php", function(data) {
                $('#stockData').html($(data).find('#stockData').html());
                $('#lastUpdated').text("Last Updated: " + new Date().toLocaleString());
            });
        }

        setInterval(updateStockData, 5000); // Refresh every 5 seconds
    </script>
</body>
</html>
