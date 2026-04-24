# LocationTracker

A location tracking system with an Android app and PHP web interface.

## Components

### Android App (`/app`)
- Tracks user location using GPS
- Sends coordinates to the web server
- Built with Gradle

### Web Interface (`/web`)
- PHP-based login and map display
- Shows all user locations on a configurable world map
- REST API for location submissions

## Quick Start

### Web Server
```bash
cd web
php -S localhost:8000
```

### Android App
```bash
./gradlew assembleDebug
```

## Documentation

- [Web README](web/README.md) - Web interface documentation
- [App README](app/README.md) - Android app documentation
- [AGENTS.md](AGENTS.md) - Development instructions for AI assistants