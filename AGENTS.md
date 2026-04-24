# AGENTS.md

## Build & Run
- `./gradlew assembleDebug` - Build debug APK
- `./gradlew assembleRelease` - Build release APK
- `./gradlew clean` - Clean build artifacts

## Project Structure
- `app/src/main/java/com/example/locationtracker/MainActivity.java` - Main entry point
- `app/src/main/res/` - Resources (layouts, strings, styles)
- `app/build.gradle` - App-level build config (compileSdk 34, minSdk 21)
- `build.gradle` - Root build config (AGP 8.1.0)

## Key Dependencies
- androidx.appcompat:appcompat:1.6.1
- com.google.android.material:material:1.11.0
- com.google.android.gms:play-services-location:21.1.0

## SDK
- Requires Android SDK at path specified in `local.properties` (currently `/opt/android-sdk`)
