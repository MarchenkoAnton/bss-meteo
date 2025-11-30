<?php
declare(strict_types=1);

namespace Bermuda\BssMeteoWidget\Utility;

/**
 * WeatherDownloader
 *
 * Відповідає ТІЛЬКИ за завантаження сирого CSV-файлу
 * для конкретного параметра та конкретного timestamp.
 *
 * Алгоритм та цикл timestamp (0000 → 2300) реалізується
 * в Scheduler (WeatherSchedulerTask), а не тут.
 *
 * Формат URL MeteoSwiss OGD:
 *   https://data.geo.admin.ch/ch.meteoschweiz.ogd-local-forecasting/{param}/{timestamp}/{param}_{timestamp}.csv
 *
 * Приклад:
 *   param     = tre200h0
 *   timestamp = 202511260000
 *   URL:
 *   https://data.geo.admin.ch/ch.meteoschweiz.ogd-local-forecasting/tre200h0/202511260000/tre200h0_202511260000.csv
 */
class WeatherDownloader
{
    /**
     * Формує OGD URL для завантаження CSV.
     *
     * @param string $param     Код параметра, напр. "tre200h0"
     * @param string $timestamp Формат YYYYMMDDHHMM, напр. "202511260000"
     */
    protected function buildUrl(string $param, string $timestamp): string
    {
        return sprintf(
            'https://data.geo.admin.ch/ch.meteoschweiz.ogd-local-forecasting/%s/%s/%s_%s.csv',
            $param,
            $timestamp,
            $param,
            $timestamp
        );
    }

    /**
     * Завантажує сирий CSV для (param, timestamp).
     *
     * НІЧОГО не знає про частоту, state, цикли та оновлення.
     * Якщо файл недоступний або сталася помилка — повертає
     * порожній рядок, щоб Scheduler/Parser/Writer не падали.
     *
     * @param string $param     Код параметра, напр. "tre200h0"
     * @param string $timestamp Формат YYYYMMDDHHMM
     * @return string Сирий CSV або порожній рядок
     */
    public function download(string $param, string $timestamp): string
    {
        $url = $this->buildUrl($param, $timestamp);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FAILONERROR    => false, // 404/500 → не фатальні для нашої логіки
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 200 OK і є контент → повертаємо CSV
        if ($httpCode === 200 && is_string($data) && $data !== '') {
            return $data;
        }

        // Інакше — вважаємо, що файлу немає / сталася помилка
        return '';
    }
}
