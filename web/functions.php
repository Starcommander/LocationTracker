<?php
define('DATA_DIR', __DIR__ . '/data');
define('SETTINGS_FILE', __DIR__ . '/settings.conf');

function loadSettings(): array {
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }

    $defaults = [
        'coordinates' => [
            'lat_min' => -90,
            'lat_max' => 90,
            'lon_min' => -180,
            'lon_max' => 180,
        ],
        'map' => [
            'width' => 2024,
            'height' => 2024,
        ],
        'users' => [
            'admin' => 'changeme',
            'demo' => 'demo',
        ],
    ];

    $settings = $defaults;

    if (file_exists(SETTINGS_FILE)) {
        $parsed = parse_ini_file(SETTINGS_FILE, true);
        if ($parsed) {
            if (isset($parsed['coordinates'])) {
                $settings['coordinates'] = array_merge($settings['coordinates'], $parsed['coordinates']);
            }
            if (isset($parsed['map'])) {
                $settings['map'] = array_merge($settings['map'], $parsed['map']);
            }
            if (isset($parsed['users'])) {
                $settings['users'] = $parsed['users'];
            }
        }
    }

    foreach ($settings['coordinates'] as $key => $value) {
        $settings['coordinates'][$key] = floatval($value);
    }
    foreach ($settings['map'] as $key => $value) {
        $settings['map'][$key] = intval($value);
    }

    return $settings;
}

function getMapWidth(): int {
    return loadSettings()['map']['width'];
}

function getMapHeight(): int {
    return loadSettings()['map']['height'];
}

function getLatMin(): float {
    return loadSettings()['coordinates']['lat_min'];
}

function getLatMax(): float {
    return loadSettings()['coordinates']['lat_max'];
}

function getLonMin(): float {
    return loadSettings()['coordinates']['lon_min'];
}

function getLonMax(): float {
    return loadSettings()['coordinates']['lon_max'];
}

function getUsers(): array {
    return loadSettings()['users'];
}

function getLocations(): array {
    $file = DATA_DIR . '/locations.json';
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true) ?: [];
    }
    return [];
}

function saveLocations(array $locations): void {
    file_put_contents(DATA_DIR . '/locations.json', json_encode($locations, JSON_PRETTY_PRINT));
}

function latLonToPixel(float $lat, float $lon): array {
    $lat = max(getLatMin(), min(getLatMax(), $lat));
    $lon = max(getLonMin(), min(getLonMax(), $lon));

    $width = getMapWidth();
    $height = getMapHeight();

    $x = (($lon - getLonMin()) / (getLonMax() - getLonMin())) * $width;
    $y = ((getLatMax() - $lat) / (getLatMax() - getLatMin())) * $height;

    return [(int)$x, (int)$y];
}

function getCornerLabel(int $corner): string {
    switch ($corner) {
        case 0: return sprintf('NW: %.0f°N, %.0f°W', getLatMax(), abs(getLonMin()));
        case 1: return sprintf('NE: %.0f°N, %.0f°E', getLatMax(), getLonMax());
        case 2: return sprintf('SW: %.0f°S, %.0f°W', abs(getLatMin()), abs(getLonMin()));
        case 3: return sprintf('SE: %.0f°S, %.0f°E', abs(getLatMin()), getLonMax());
        default: return '';
    }
}

function generateBaseMap(): string {
    $mapFile = DATA_DIR . '/map.png';

    if (!file_exists($mapFile)) {
        $width = getMapWidth();
        $height = getMapHeight();

        $image = imagecreatetruecolor($width, $height);

        $ocean = imagecolorallocate($image, 0, 100, 180);
        $grid = imagecolorallocate($image, 255, 255, 255);
        $text = imagecolorallocate($image, 255, 255, 0);

        imagefill($image, 0, 0, $ocean);

        $gridSpacing = $width / 12;
        for ($i = 1; $i < 12; $i++) {
            $x = $i * $gridSpacing;
            imageline($image, $x, 0, $x, $height, $grid);
        }

        $gridSpacing = $height / 6;
        for ($i = 1; $i < 6; $i++) {
            $y = $i * $gridSpacing;
            imageline($image, 0, $y, $width, $y, $grid);
        }

        $fontSize = 5;
        $lonStep = 30;
        for ($lon = getLonMin(); $lon <= getLonMax(); $lon += $lonStep) {
            $pos = latLonToPixel(0, $lon);
            imagestring($image, $fontSize, $pos[0] + 5, $height / 2 + 5, $lon . '°', $text);
        }

        $latStep = 30;
        for ($lat = getLatMin(); $lat <= getLatMax(); $lat += $latStep) {
            $pos = latLonToPixel($lat, 0);
            imagestring($image, $fontSize, 5, $pos[1] - 10, $lat . '°', $text);
        }

        imagestring($image, $fontSize, 10, 10, getCornerLabel(0), $text);
        imagestring($image, $fontSize, $width - 150, 10, getCornerLabel(1), $text);
        imagestring($image, $fontSize, 10, $height - 25, getCornerLabel(2), $text);
        imagestring($image, $fontSize, $width - 150, $height - 25, getCornerLabel(3), $text);

        imagepng($image, $mapFile);
        imagedestroy($image);
    }

    return $mapFile;
}

function generateMapWithUsers(): string {
    $mapFile = DATA_DIR . '/map.png';
    $outputFile = DATA_DIR . '/map_with_users.png';

    $baseMap = generateBaseMap();
    $image = imagecreatefrompng($mapFile);

    $dotColor = imagecolorallocate($image, 255, 0, 0);
    $textColor = imagecolorallocate($image, 255, 255, 255);
    $outlineColor = imagecolorallocate($image, 0, 0, 0);

    $locations = getLocations();

    foreach ($locations as $name => $location) {
        $lat = $location['lat'];
        $lon = $location['lon'];
        $pos = latLonToPixel($lat, $lon);

        $x = $pos[0];
        $y = $pos[1];

        imagefilledellipse($image, $x, $y, 16, 16, $outlineColor);
        imagefilledellipse($image, $x, $y, 12, 12, $dotColor);

        imagestring($image, 3, $x + 10, $y - 10, htmlspecialchars($name), $textColor);
    }

    imagepng($image, $outputFile);
    imagedestroy($image);

    return $outputFile;
}