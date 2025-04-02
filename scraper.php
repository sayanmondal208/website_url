<?php
// Database Configuration
$servername = "localhost";
$username = "root";  // Default for XAMPP
$password = "";      // Default is empty in XAMPP
$database = "web_scraper";


// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch website data
function fetchWebsiteData($url) {
    $html = @file_get_contents($url);

    if ($html === false) {
        die("❌ Failed to fetch URL");
    }

    // Extract Words
    $text = strip_tags($html);
    preg_match_all('/\b\w+\b/', $text, $matches);
    $words = implode(", ", $matches[0]);

    // Extract Image URLs
    preg_match_all('/<img[^>]+src=["\']?([^"\' >]+)["\']?/i', $html, $imageMatches);
    $images = implode(", ", $imageMatches[1]);

    return [
        'words' => $words,
        'images' => $images,
        'full_content' => $html
    ];
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $url = $_POST["url"];

    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        die("❌ Invalid URL. Please enter a valid website.");
    }

    // Fetch website data
    $data = fetchWebsiteData($url);

    // Store data in MySQL
    $stmt = $conn->prepare("INSERT INTO website_data (url, words, images, full_content) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $url, $data['words'], $data['images'], $data['full_content']);
    $stmt->execute();

    echo "✅ Website data stored successfully!";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Website Scraper</title>
</head>
<body>
    <h2>Enter a Website URL to Scrape Data</h2>
    <form method="POST">
        <input type="text" name="url" required placeholder="Enter Website URL">
        <button type="submit">Scrape & Store</button>
    </form>
</body>
</html>
