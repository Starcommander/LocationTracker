<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

if (!is_writable(DATA_DIR)) {
    die('Error: Data directory is not writable: ' . DATA_DIR);
}

if (!extension_loaded('gd')) {
    die('Error: GD extension is not loaded. Please install php-gd.');
}

$mapImage = generateMapWithUsers();
$mapUrl = 'data/map_with_users.png?t=' . time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map - Location Tracker</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #1a1a2e;
            min-height: 100vh;
            color: white;
        }
        .header {
            background: #16213e;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 24px;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .logout {
            color: #ff6b6b;
            text-decoration: none;
            padding: 8px 16px;
            border: 1px solid #ff6b6b;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .logout:hover {
            background: #ff6b6b;
            color: white;
        }
        .container {
            padding: 20px;
        }
        .map-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            text-align: center;
        }
        .map-container img {
            max-width: 100%;
            height: auto;
        }
        .location-form {
            background: #16213e;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .location-form h2 {
            margin-bottom: 15px;
        }
        .form-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #16213e;
        }
        button {
            padding: 12px 24px;
            background: #e94560;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #ff6b6b;
        }
        .coordinates {
            background: #16213e;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .coordinates h2 {
            margin-bottom: 15px;
        }
        .coord-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
        }
        .coord-item {
            background: #1a1a2e;
            padding: 10px;
            border-radius: 4px;
        }
        .coord-item strong {
            color: #e94560;
        }
        .success-message {
            background: #4CAF50;
            color: white;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Location Tracker - Live Map</h1>
        <div class="user-info">
            <span>Logged in as: <strong><?= htmlspecialchars($_SESSION['user']) ?></strong></span>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="location-form">
            <h2>Submit Your Location</h2>
            <div class="success-message" id="successMessage"></div>
            <form id="locationForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($_SESSION['user']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="latitude">Latitude (-90 to 90)</label>
                        <input type="number" id="latitude" name="latitude" step="any" min="-90" max="90" required>
                    </div>
                    <div class="form-group">
                        <label for="longitude">Longitude (-180 to 180)</label>
                        <input type="number" id="longitude" name="longitude" step="any" min="-180" max="180" required>
                    </div>
                </div>
                <button type="submit">Update Location</button>
            </form>
        </div>

        <div class="coordinates">
            <h2>Current User Locations</h2>
            <div class="coord-grid" id="coordGrid">
                <?php
                $locations = getLocations();
                foreach ($locations as $name => $loc):
                ?>
                <div class="coord-item">
                    <strong><?= htmlspecialchars($name) ?></strong>: 
                    <?= htmlspecialchars($loc['lat']) ?>, <?= htmlspecialchars($loc['lon']) ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="map-container">
            <img src="<?= $mapUrl ?>" alt="Map with user locations">
        </div>
    </div>

    <script>
        document.getElementById('locationForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = {
                name: formData.get('name'),
                lat: parseFloat(formData.get('latitude')),
                lon: parseFloat(formData.get('longitude'))
            };

            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    document.getElementById('successMessage').textContent = 'Location updated successfully!';
                    document.getElementById('successMessage').style.display = 'block';
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error submitting location');
            }
        });

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
                    document.getElementById('longitude').value = position.coords.longitude.toFixed(6);
                },
                function(error) {
                    console.log('Geolocation error: ' + error.message);
                }
            );
        }
    </script>
</body>
</html>