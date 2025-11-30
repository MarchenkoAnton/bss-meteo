<?php
declare(strict_types=1);

namespace Bermuda\BssMeteoWidget\Task;

use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Bermuda\BssMeteoWidget\Utility\WeatherDownloader;
use Bermuda\BssMeteoWidget\Utility\WeatherParser;
use Bermuda\BssMeteoWidget\Utility\WeatherWriter;
use Bermuda\BssMeteoWidget\Utility\ParameterRegistry;

class WeatherSchedulerTask extends AbstractTask
{
    public function execute(): bool
    {
        /** @var ParameterRegistry $registry */
        $registry  = GeneralUtility::makeInstance(ParameterRegistry::class);
        $downloader = GeneralUtility::makeInstance(WeatherDownloader::class);
        $parser     = GeneralUtility::makeInstance(WeatherParser::class);
        $writer     = GeneralUtility::makeInstance(WeatherWriter::class);

        // Отримуємо конфігурації віджетів (point_id + параметри з tt_content)
        $rows = $this->fetchWidgets();
        if (empty($rows)) {
            $this->log("No active MeteoSwiss widgets");
            return true;
        }

        $today = date('Ymd'); // тільки поточна дата

        foreach ($rows as $row) {
            $pointId   = (int)($row['point_id']);
            $paramsRaw = (string)($row['parameters']);
            $params    = array_filter(array_map('trim', explode(',', $paramsRaw)));

            if ($pointId <= 0 || empty($params)) {
                continue;
            }

            $this->log(sprintf("→ Widget point_id=%d", $pointId));

            foreach ($params as $param) {
                $frequency = $registry->getFrequency($param) ?: 'unknown';
                $updatedForParam = false;

                // 👇 Цикл timestamp: 0000 → 2300 → 2200 → ... → 0100
                $hourSteps = [0];
                for ($h = 23; $h >= 1; $h--) {
                    $hourSteps[] = $h * 100;
                }

                foreach ($hourSteps as $h) {
                    $timestamp = sprintf('%s%04d', $today, $h);

                    $csv = $downloader->download($param, $timestamp);
                    if ($csv === '') {
                        continue; // не break — інакше будуть пропуски у нашому алгоритмі
                    }

                    $rowsParsed = $parser->parse($csv, $pointId);
                    if (!empty($rowsParsed)) {
                        $written = $writer->write($pointId, $param, $rowsParsed, $frequency);
                        if ($written) {
                            $updatedForParam = true;
                        }
                    }
                }

                if ($updatedForParam) {
                    $this->log(sprintf('   ✔ %s updated', $param));
                } else {
                    $this->log(sprintf('   • %s no change', $param));
                }
            }
        }

        return true;
    }

    /**
     * Отримує активні виджети з tt_content
     * Очікується, що flexform зберігає point_id and parameters (CSV-рядок)
     */
    protected function fetchWidgets(): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');

        return $queryBuilder
            ->select('uid', 'point_id', 'parameters')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('weatherwidget_meteoswiss'))
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }
}
