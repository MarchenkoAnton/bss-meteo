<?php
declare(strict_types=1);

namespace Bermuda\BssMeteoWidget\Utility;

use TYPO3\CMS\Core\Core\Environment;

/**
 * WeatherWriter
 *
 * Пише JSON лише якщо каталог існує.
 * НЕ створює каталоги і НЕ видаляє файли при пустих даних.
 * Стабільна поведінка — як у продакшені.
 */
class WeatherWriter
{
    /**
     * @param int $pointId
     * @param string $param
     * @param array $forecastRows
     * @param string $frequency hourly|3hourly|daily
     * @return bool true — файл оновлено / false — пропущено
     */
    public function write(int $pointId, string $param, array $forecastRows, string $frequency): bool
    {
        $basePath = Environment::getPublicPath()
            . '/fileadmin/meteoswiss/'
            . $frequency . '/'
            . $param . '/';

        // Якщо каталогу немає — нічого не робимо (виключно рішення бекенда v7/v8)
        if (!is_dir($basePath)) {
            return false;
        }

        $targetFile = $basePath . $pointId . '.json';
        $tmpFile    = $targetFile . '.tmp';

        $json = json_encode(
            $forecastRows,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        // JSON encode error — нічого не робимо
        if ($json === false) {
            return false;
        }

        // Якщо файл є → порівнюємо, щоб уникати зайвого перезапису
        if (is_file($targetFile)) {
            $old = file_get_contents($targetFile);
            if (is_string($old) && $old === $json) {
                return false; // без змін
            }
        }

        // Атомарний запис через .tmp
        file_put_contents($tmpFile, $json);
        rename($tmpFile, $targetFile);

        return true;
    }
}
