<?php
declare(strict_types=1);

namespace Bermuda\BssMeteoWidget\Utility;

/**
 * WeatherParser
 *
 * Приймає CSV і повертає масив forecast-рядків ТІЛЬКИ для потрібного point_id.
 *
 * CSV формат MeteoSwiss (незмінний):
 *   point_id;point_type_id;Date;{param}
 *
 * Повертає масив у стилі v7/v8 (стабільний та сумісний з Writer):
 *
 * [
 *   [
 *     "point_id"      => 405,
 *     "point_type_id" => 1,
 *     "Date"          => 202511252100,
 *     "tre200h0"      => -0.9
 *   ],
 *   ...
 * ]
 */
class WeatherParser
{
    /**
     * @param string $csv CSV контент
     * @param int $pointId Цільова метеостанція
     * @return array Список структурованих записів, або []
     */
    public function parse(string $csv, int $pointId): array
    {
        // CSV порожній або збій — одразу повертаємо []
        if (trim($csv) === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($csv));
        if (!$lines || count($lines) < 2) {
            return [];
        }

        // Парсимо заголовок
        $header = str_getcsv(array_shift($lines), ';');
        $columnCount = count($header);

        // Стандартизувати заголовки (без пробілів)
        $header = array_map('trim', $header);

        // Index-и колонок
        $colIndex = array_flip($header);

        // Очікувані стовпці
        if (
            !isset($colIndex['point_id']) ||
            !isset($colIndex['point_type_id']) ||
            !isset($colIndex['Date'])
        ) {
            return [];
        }

        $result = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $cols = str_getcsv($line, ';');

            // якщо рядок некоректний
            if (count($cols) !== $columnCount) {
                continue;
            }

            // фільтрація по point_id
            $currentPoint = (int)($cols[$colIndex['point_id']] ?? -1);
            if ($currentPoint !== $pointId) {
                continue;
            }

            // формуємо асоціативний масив
            $row = [];
            foreach ($header as $i => $name) {
                $row[$name] = $cols[$i] ?? null;
            }

            // Приводимо типи до стабільного формату:
            // point_id і point_type_id → int
            // Date → int (не форматувати!)
            if (isset($row['point_id'])) {
                $row['point_id'] = (int)$row['point_id'];
            }
            if (isset($row['point_type_id'])) {
                $row['point_type_id'] = (int)$row['point_type_id'];
            }
            if (isset($row['Date'])) {
                $row['Date'] = (int)$row['Date'];
            }

            // Додаємо до результату
            $result[] = $row;
        }

        return $result;
    }
}
