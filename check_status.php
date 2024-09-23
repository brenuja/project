<?php
// Function to check if the website is up or down
function checkWebsiteStatus($url) {
    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return [
            "status" => "invalid_url",
            "message" => "The URL provided is not valid."
        ];
    }

    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout after 10 seconds
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects

    // Execute the request
    curl_exec($ch);

    // Get the HTTP status code
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch); // Get cURL error if any
    curl_close($ch);

    // Check for any cURL errors
    if ($error) {
        return [
            "status" => "down",
            "message" => "Error: $error"
        ];
    }

    // Check if the website is up (HTTP status 200-399)
    if ($httpcode >= 200 && $httpcode < 400) {
        return [
            "status" => "up",
            "message" => "Website is up with status code $httpcode."
        ];
    } else {
        return [
            "status" => "down",
            "message" => "Website is down with status code $httpcode."
        ];
    }
}

// Check if URL is passed
if (isset($_POST['url'])) {
    $url = $_POST['url'];
    $status = checkWebsiteStatus($url);

    // Return the status as a JSON response
    echo json_encode($status);
} else {
    // No URL provided
    echo json_encode([
        "status" => "error",
        "message" => "No URL provided."
    ]);
}
?>
