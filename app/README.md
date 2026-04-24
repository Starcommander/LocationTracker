# Location Tracker - Android App

An Android application for tracking and sharing user locations.

## Build

```bash
cd ..
./gradlew assembleDebug
```

The APK will be in `app/build/outputs/apk/debug/`

## Features

- Location tracking using Google Play Services
- Send location to web server
- Supports fine and coarse location permissions

## Permissions

- `ACCESS_FINE_LOCATION` - Precise GPS location
- `ACCESS_COARSE_LOCATION` - Approximate network-based location

## Configuration

Set your server URL in `MainActivity.java`:
```java
private static final String API_URL = "http://your-server/api.php";
```

## Architecture

```
java/com/example/locationtracker/
└── MainActivity.java    # Main activity with location tracking

res/
├── layout/             # XML layouts
├── values/             # Strings and styles
└── drawable/          # Graphics
```