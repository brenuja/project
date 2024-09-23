<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert Settings</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Basic styling for the circle and status indicator */
        .status-indicator {
            display: flex;
            align-items: center;
            margin-top: 20px;
        }

        .circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
            background-color: #28a745; /* Default color: green for up */
            animation: blink 1s infinite alternate;
        }

        @keyframes blink {
            0% { opacity: 1; }
            100% { opacity: 0.7; }
        }
    </style>
</head>
<body>
    <!-- Form for submitting alert settings -->
    <div class="form-container" id="form-container">
        <form id="alertForm" action="process_alert.php" method="POST" onsubmit="return handleFormSubmit(event)">
            <h2>Set Up Alerts</h2>

            <!-- "Alert When" Dropdown -->
            <div class="input-group">
                <label for="alert_when">Alert When</label>
                <select id="alert_when" name="alert_when" required>
                    <option value="">Select an alert condition</option>
                    <option value="site_down">Site is down</option>
                    <option value="high_response_time">High response time</option>
                    <option value="new_incident">New incident</option>
                </select>
            </div>

            <!-- "URL to Monitor" Input -->
            <div class="input-group">
                <label for="monitor_url">URL to Monitor</label>
                <input type="url" id="monitor_url" name="monitor_url" placeholder="Enter the URL to monitor" required>
            </div>

            <!-- "Notification Preferences" Checkboxes -->
            <div class="input-group">
                <label for="notifications">When there is a new incident:</label>
                <div class="checkbox-group">
                    

                    <input type="checkbox" id="send_email" name="notify[]" value="send_email">
                    <label for="send_email">Send Email</label>
                </div>
            </div>

            <button type="submit">Submit Alert Settings</button>
        </form>
    </div>

    <!-- Hidden content that shows after submission -->
    <div class="status-container" id="status-container" style="display: none;">
        <div class="status-box">
            <h2 id="submittedUrl">Website URL: </h2>

            <!-- Blinking green circle -->
            <div class="status-indicator">
                <div class="circle"></div>
                <span id="urlStatus">Up Status</span>
            </div>
            <p id="statusText">Status: Up</p>

            <p>Checked every three minutes</p>
        </div>
    </div>

    <!-- JavaScript code for form submission and status checking -->
    <script>
        function handleFormSubmit(event) {
            event.preventDefault();

            const url = document.getElementById('monitor_url').value;
            
            // Hide form and show the status section
            document.getElementById('form-container').style.display = 'none';
            document.getElementById('status-container').style.display = 'block';

            // Set the website URL in the status box
            document.getElementById('submittedUrl').innerHTML = 'Website URL: ' + url;

            // Start checking the website status
            checkWebsiteStatus(url);

            // Check status every 3 minutes (180000 ms)
            setInterval(function() {
                checkWebsiteStatus(url);
            }, 180000);
        }

        function checkWebsiteStatus(url) {
            // Send an AJAX request to check the website status
            fetch('check_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'url': url
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "up") {
                    document.getElementById('urlStatus').textContent = 'Up Status';
                    document.getElementById('statusText').textContent = 'Status: Up';
                    document.querySelector('.circle').style.backgroundColor = '#28a745'; // Green color for up
                } else {
                    document.getElementById('urlStatus').textContent = 'Down Status';
                    document.getElementById('statusText').textContent = 'Status: Down';
                    document.querySelector('.circle').style.backgroundColor = '#dc3545'; // Red color for down
                }
            })
            .catch(error => {
                console.error('Error checking website status:', error);
            });
        }
    </script>
</body>
</html>
