<?php
namespace Bermuda\BssMeteoWidget\Utility;

use TYPO3\CMS\Core\Core\Environment;

class StationList
{
    private array $stations = [];
    private bool $loaded = false;

    /**
     * Loads station dictionary from JSON once per request
     */
    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $file = Environment::getPublicPath()
            . '/typo3conf/ext/bss_meteo_widget/Resources/Public/Assets/weather_stations_min.json';

        if (!is_file($file)) {
            $this->stations = [];
            $this->loaded = true;
            return;
        }

        $json = file_get_contents($file);
        $decoded = json_decode($json, true);

        // Format {"63": {...}, "464": {...}}
        $this->stations = is_array($decoded) ? $decoded : [];
        $this->loaded = true;
    }

    /**
     * Return full station list
     */
    public function getAll(): array
    {
        $this->load();
        return $this->stations;
    }

    /**
     * Get station data by point_id
     */
    public function getById(int $pointId): ?array
    {
        $this->load();
        return $this->stations[$pointId] ?? null;
    }

    /**
     * Check if station exists
     */
    public function exists(int $pointId): bool
    {
        $this->load();
        return isset($this->stations[$pointId]);
    }

    /**
     * Get visual display name (uses station_name field)
     */
    public function getName(int $pointId): ?string
    {
        $station = $this->getById($pointId);
        return $station['station_name'] ?? null;
    }

    /**
     * Short 3-letter code (like ABO, AEG)
     */
    public function getCode(int $pointId): ?string
    {
        $station = $this->getById($pointId);
        return $station['station_id'] ?? null;
    }

    /**
     * Canton code (BE, ZG, etc.)
     */
    public function getCanton(int $pointId): ?string
    {
        $station = $this->getById($pointId);
        return $station['canton'] ?? null;
    }

    /**
     * Coordinates (lat / lon) for map / future heatmap
     */
    public function getCoords(int $pointId): ?array
    {
        $station = $this->getById($pointId);

        return $station
            ? ['lat' => $station['lat'], 'lon' => $station['lon']]
            : null;
    }

    /**
     * Altitude (meters above sea level)
     */
    public function getAltitude(int $pointId): ?float
    {
        $station = $this->getById($pointId);
        return $station['altitude_masl'] ?? null;
    }
}
