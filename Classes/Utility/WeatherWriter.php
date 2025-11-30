<?php
namespace Bermuda\BssMeteoWidget\Utility;

/**
 * WeatherWriter
 *
 * Writes parsed data into JSON at fileadmin/meteoswiss/{frequency}/{param}/{pointId}.json
 */
class WeatherWriter
{
    /**
     * TODO: Write JSON forecast file
     *
     * @param int $pointId
     * @param array $forecast
     */
    public function write(int $pointId, array $forecast): void
    {
        // no-op
    }
}
