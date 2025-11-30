<?php
declare(strict_types=1);

namespace Bermuda\BssMeteoWidget\Utility;

/**
 * ParameterRegistry
 *
 * Provides list of supported MeteoSwiss parameters and their frequencies.
 * Used by Downloader → Parser → Writer → Scheduler → Frontend.
 */
class ParameterRegistry
{
    private array $params = [
        // ---------------------------------------------------------
        // HOURLY PARAMETERS
        // ---------------------------------------------------------
        'tre200h0' => [
            'label'     => 'Air Temperature 2m (hourly mean)',
            'frequency' => 'hourly',
        ],
        'fu3010h0' => [
            'label'     => 'Wind Speed (hourly mean)',
            'frequency' => 'hourly',
        ],
        'fu3010h1' => [
            'label'     => 'Wind Gusts (hourly max)',
            'frequency' => 'hourly',
        ],
        'dkl010h0' => [
            'label'     => 'Wind Direction (hourly mean)',
            'frequency' => 'hourly',
        ],
        'rre150h0' => [
            'label'     => 'Precipitation (hourly sum)',
            'frequency' => 'hourly',
        ],
        'nprolohs' => [
            'label'     => 'Low Cloud Cover (hourly mean)',
            'frequency' => 'hourly',
        ],
        'npromths' => [
            'label'     => 'Mid Cloud Cover (hourly mean)',
            'frequency' => 'hourly',
        ],
        'nprohihs' => [
            'label'     => 'High Cloud Cover (hourly mean)',
            'frequency' => 'hourly',
        ],
        'sre000h0' => [
            'label'     => 'Sunshine Duration (hourly)',
            'frequency' => 'hourly',
        ],
        'gre000h0' => [
            'label'     => 'Global Radiation (hourly mean)',
            'frequency' => 'hourly',
        ],
        'zprfr0hs' => [
            'label'     => 'Freezing Level (hourly value)',
            'frequency' => 'hourly',
        ],

        // ---------------------------------------------------------
        // 3-HOURLY PARAMETERS
        // ---------------------------------------------------------
        'jww003i0' => [
            'label'     => 'Weather Symbol (3h forecast)',
            'frequency' => '3hourly',
        ],
        'rp0003i0' => [
            'label'     => 'Precipitation Probability (3h)',
            'frequency' => '3hourly',
        ],

        // ---------------------------------------------------------
        // DAILY PARAMETERS
        // ---------------------------------------------------------
        'jp2000d0' => [
            'label'     => 'Daily Weather Symbol',
            'frequency' => 'daily',
        ],
        'rka150p0' => [
            'label'     => 'Precipitation (daily sum)',
            'frequency' => 'daily',
        ],
        'tre200dn' => [
            'label'     => 'Daily Minimum Temperature',
            'frequency' => 'daily',
        ],
        'tre200dx' => [
            'label'     => 'Daily Maximum Temperature',
            'frequency' => 'daily',
        ],
    ];

    /**
     * Return all parameters
     */
    public function getAll(): array
    {
        return $this->params;
    }

    /**
     * Return mapping paramCode => frequency
     * (used by SetupCommand to create directory structure)
     */
    public function getAllParamsWithFrequency(): array
    {
        $result = [];
        foreach ($this->params as $code => $meta) {
            $result[$code] = $meta['frequency'];
        }
        return $result;
    }

    /**
     * Return one parameter by code
     */
    public function get(string $param): ?array
    {
        return $this->params[$param] ?? null;
    }

    /**
     * Return update frequency ("hourly", "3hourly", "daily")
     */
    public function getFrequency(string $param): ?string
    {
        return $this->params[$param]['frequency'] ?? null;
    }

    /**
     * Return human-readable label
     */
    public function getLabel(string $param): ?string
    {
        return $this->params[$param]['label'] ?? null;
    }

    /**
     * Check if parameter exists
     */
    public function exists(string $param): bool
    {
        return isset($this->params[$param]);
    }
}
