// api/fetchNiftyData.js

const axios = require('axios');

module.exports = async (req, res) => {
    const url = "https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%2050";
    
    try {
        const response = await axios.get(url, {
            headers: {
                "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
                "Accept": "application/json, text/plain, */*",
                "Referer": "https://www.nseindia.com/",
                "X-Requested-With": "XMLHttpRequest"
            }
        });
        
        res.status(200).json(response.data);
    } catch (error) {
        res.status(500).json({ error: "Failed to fetch data" });
    }
};
