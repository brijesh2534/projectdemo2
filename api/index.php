<?php
// Set the URL of the NSE API
$nse_url = "https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%20500";

// Function to fetch stock data from the NSE API
function fetchStockData($url) {
    // Set HTTP headers, particularly the User-Agent
    $options = [
        "http" => [
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.82 Safari/537.36\r\n"
        ]
    ];

    // Create a stream context with the specified options
    $context = stream_context_create($options);
    
    // Fetch the response from the API, suppress errors with @
    $response = @file_get_contents($url, false, $context);

    // Check if the response is false (an error occurred)
    if ($response === false) {
        return null; // Return null to indicate failure
    }

    // Decode the JSON response into an associative array
    $data = json_decode($response, true);
    return $data['data'] ?? null; // Return the 'data' key if it exists
}

// Fetch the stock data from the API
$data = fetchStockData($nse_url);

// Check if the data was fetched successfully
if ($data === null) {
    echo "<h2>Error: Could not fetch stock data.</h2>";
    echo "<p>Please check if the NSE API is accessible.</p>";
    exit; // Stop further execution
}

// Proceed to display the fetched stock data
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NSE Stock Data</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1 class="mt-4">NSE Stock Data</h1>

        <table class="table table-striped mt-4">
            <thead>
                <tr>
                    <th>Symbol</th>
                    <th>Last Price</th>
                    <th>Change</th>
                    <th>Change Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $stock): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($stock['symbol']); ?></td>
                        <td><?php echo htmlspecialchars($stock['lastPrice']); ?></td>
                        <td><?php echo htmlspecialchars($stock['change']); ?></td>
                        <td><?php echo htmlspecialchars($stock['pChange']); ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
