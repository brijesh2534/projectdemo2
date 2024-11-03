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
        <p class="text-center" id="lastUpdated">Last Updated: <span id="time"></span></p>
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
                <tr>
                    <td colspan="4" class="text-center">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        const userAgents = [
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) Gecko/20100101 Firefox/89.0",
            "Mozilla/5.0 (Linux; Android 10; Pixel 3 XL Build/QP1A.191005.007) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.127 Mobile Safari/537.36",
            "Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Safari/605.1.15"
        ];

        function getRandomUserAgent() {
            return userAgents[Math.floor(Math.random() * userAgents.length)];
        }

        function fetchStockData() {
            const randomDelay = Math.floor(Math.random() * 5000) + 1000; // Random delay between 1-6 seconds
            setTimeout(() => {
                $.ajax({
                    url: "https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%2050",
                    method: "GET",
                    headers: {
                        "User-Agent": getRandomUserAgent(),
                        "Accept": "application/json, text/plain, */*",
                        "Referer": "https://www.nseindia.com/"
                    },
                    success: function(data) {
                        const stocks = data.data.filter(stock => stock.pChange > 0);
                        stocks.sort((a, b) => b.pChange - a.pChange);
                        const stockRows = stocks.slice(0, 10).map(stock => `
                            <tr>
                                <td>${stock.symbol}</td>
                                <td>${stock.companyName || stock.symbol}</td>
                                <td>₹${stock.lastPrice}</td>
                                <td>${stock.pChange}%</td>
                            </tr>
                        `).join("");
                        $("#stockData").html(stockRows);
                        $("#time").text(new Date().toLocaleString());
                    },
                    error: function() {
                        $("#stockData").html("<tr><td colspan='4' class='text-center'>Error fetching data. Please try again later.</td></tr>");
                    }
                });
            }, randomDelay);
        }

        fetchStockData();
        setInterval(fetchStockData, 3000); // Refresh every 30 seconds
    </script>
</body>
</html>
