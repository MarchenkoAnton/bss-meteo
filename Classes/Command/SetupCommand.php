<?php
declare(strict_types=1);

namespace Bermuda\BssMeteoWidget\Command;

use Bermuda\BssMeteoWidget\Utility\ParameterRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SetupCommand extends Command
{
    protected static $defaultName = 'bss-meteo:setup';

    protected function configure(): void
    {
        $this->setDescription(
            'Initializes directory structure and deploys assets into public/fileadmin/meteoswiss'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $extPath    = ExtensionManagementUtility::extPath('bss_meteo_widget');
        $assetsPath = $extPath . 'Resources/Public/Assets/';
        $iconsPath  = $assetsPath . 'WeatherIcons/';

        $base = Environment::getPublicPath() . '/fileadmin/meteoswiss/';

        /** @var ParameterRegistry $registry */
        $registry = GeneralUtility::makeInstance(ParameterRegistry::class);
        $params   = $registry->getAllParamsWithFrequency();   // param => frequency

        //
        // 1) Base directory
        //
        if (!is_dir($base)) {
            mkdir($base, 0775, true);
        }
        $output->writeln('<info>✓ Base directory ready</info>');

        //
        // 2) Frequency folders (hourly, 3hourly, daily, unknown)
        //
        $frequencies = ['hourly', '3hourly', 'daily', 'unknown'];
        foreach ($frequencies as $freq) {
            $path = $base . $freq . '/';
            if (!is_dir($path)) {
                mkdir($path, 0775, true);
            }
        }
        $output->writeln('<info>✓ Frequency directories ready</info>');

        //
        // 3) Parameter folders (one for each MeteoSwiss parameter)
        //
        foreach ($params as $param => $freq) {
            // Fallback to /unknown/ if registry contains an unexpected value
            if (!in_array($freq, $frequencies, true)) {
                $freq = 'unknown';
            }

            $dir = $base . $freq . '/' . $param . '/';
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
        }
        $output->writeln('<info>✓ Parameter directories created</info>');

        //
        // 4) Deploy stations.json + symbol_map.json
        //
        if (is_file($assetsPath . 'stations.json')) {
            copy($assetsPath . 'stations.json', $base . 'stations.json');
        }
        if (is_file($assetsPath . 'symbol_map.json')) {
            copy($assetsPath . 'symbol_map.json', $base . 'symbol_map.json');
        }
        $output->writeln('<info>✓ stations.json + symbol_map.json deployed</info>');

        //
        // 5) Deploy Weather Icons
        //
        $iconsTarget = $base . 'weather_icons/';
        if (!is_dir($iconsTarget)) {
            mkdir($iconsTarget, 0775, true);
        }

        if (is_dir($iconsPath)) {
            foreach (glob($iconsPath . '*.svg') as $file) {
                copy($file, $iconsTarget . basename($file));
            }
        }
        $output->writeln('<info>✓ Weather icons deployed</info>');

        //
        // 6) Create installation marker
        //
        file_put_contents($base . '.installed', date('c'));
        $output->writeln('<info>✓ Created .installed marker</info>');

        $output->writeln('<comment>🎉 BSS Meteo setup complete.</comment>');
        return Command::SUCCESS;
    }
}
