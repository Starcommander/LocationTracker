# Location Tracker - Web Interface

A PHP-based web application that displays user locations on a configurable world map.

## Quick Start

```bash
php -S localhost:8000 -t .
```

Then visit `http://localhost:8000`

## Default Credentials

| Username | Password |
|----------|----------|
| admin    | changeme |
| demo     | demo     |

**Change these in `settings.conf` before production use.**

## Configuration

Edit `settings.conf` to customize:

### Coordinates
```ini
[coordinates]
lat_min=-90
lat_max=90
lon_min=-180
lon_max=180
```

### Users
```ini
[users]
admin=your_secure_password
user2=another_password
```

### Map Size
```ini
[map]
width=2024
height=2024
```

## API

### Submit Location

```bash
POST /api.php
Content-Type: application/json

{"name": "Alice", "lat": 48.8566, "lon": 2.3522}
```

Response:
```json
{"success": true, "message": "Location saved"}
```

## Project Structure

```
web/
├── index.php      # Login page
├── map.php        # Map display
├── api.php        # Location API endpoint
├── functions.php  # Shared functions
├── settings.conf  # Configuration
├── logout.php     # Logout handler
└── data/          # Data storage
    ├── locations.json
    ├── map.png
    └── map_with_users.png
```

## Features

- User authentication
- Configurable map boundaries
- Real-time user location tracking
- Browser geolocation support
- Multiple user support