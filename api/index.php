<?php
require 'fetchStockData.php';

$nse_url = "https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%20500";
$data = fetchStockData($nse_url);

if ($data === null) {
    echo "<p>Error: Could not fetch stock data after multiple attempts.</p>";
    exit;
}

// Filter and sort top gainers
$stocks = array_filter($data, fn($stock) => $stock['pChange'] > 0);
usort($stocks, fn($a, $b) => $b['pChange'] <=> $a['pChange']);
$lastUpdated = date("Y-m-d H:i:s"); // Store last updated time
?>

<!-- HTML output for AJAX request -->
<p id="lastUpdated">Last Updated: <?php echo $lastUpdated; ?></p>
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
