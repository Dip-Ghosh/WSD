<?php

declare(strict_types = 1);

namespace Application\Services\Portfolio;

use RuntimeException;

class PortfolioService
{
    private $instrumentsFile;
    private $instrumentPropertiesFile;

    public function __construct(
        string $instrumentsFile = __DIR__.'../../../../../data/source/instruments-data.json',
        string $instrumentPropertiesFile = __DIR__.'/../../../../../data/source/instruments-properties.json'
    ) {
        $this->instrumentsFile          = $instrumentsFile;
        $this->instrumentPropertiesFile = $instrumentPropertiesFile;
    }

    public function getInstruments()
    {
        return $this->readJsonFile($this->instrumentsFile, 'instruments data');
    }

    public function getInstrumentProperties()
    {
        return $this->readJsonFile($this->instrumentPropertiesFile, 'instrument properties');
    }

    private function readJsonFile(string $filePath, string $label)
    {
        $data = file_get_contents($filePath);

        if ($data === false) {
            throw new RuntimeException(sprintf('Unable to read %s file.', $label));
        }

        $decoded = json_decode($data, true);

        if ($decoded === null) {
            throw new RuntimeException(sprintf('Invalid JSON in %s file.', $label));
        }

        return $decoded;
    }

    public function mergeData(array $instruments, array $instrumentProperties)
    {
        $portfolios = [];

        foreach ($instruments as $instrument) {
            $isin = isset($instrument['isin']) ? $instrument['isin'] : null;

            if (!$isin || !isset($instrumentProperties[$isin])) {
                continue;
            }

            $portfolios[$isin] = array_merge($instrument, $instrumentProperties[$isin]);
        }

        return $portfolios;
    }

    public function filterPortfolios(array $portfolios, $showProfits = false, $structureId = null)
    {
        if ($showProfits) {
            $portfolios = $this->filterProfitable($portfolios);
        }

        if ($structureId !== null) {
            $portfolios = $this->filterByStructure($portfolios, (int) $structureId);
        }

        return $portfolios;
    }

    private function filterProfitable(array $portfolios)
    {
        $profitable = [];

        foreach ($portfolios as $isin => $portfolio) {
            $buyPrice         = isset($portfolio['buyPrice']) ? (float) $portfolio['buyPrice'] : 0;
            $currentSellPrice = isset($portfolio['currentSellPrice']) ? (float) $portfolio['currentSellPrice'] : 0;
            $profit           = $currentSellPrice - $buyPrice;

            if ($profit > 0) {
                $portfolio['profit']           = $profit;
                $portfolio['profitPercentage'] = $this->calculateProfitPercentage($profit, $buyPrice);
                $profitable[$isin]             = $portfolio;
            }
        }

        return $profitable;
    }

    private function filterByStructure(array $portfolios, $structureId)
    {
        $filtered = [];

        foreach ($portfolios as $isin => $portfolio) {

            if (isset($portfolio['structure']) && $portfolio['structure'] === $structureId) {
                $filtered[$isin] = $portfolio;
            }
        }

        return $filtered;
    }

    private function calculateProfitPercentage($profit, $buyPrice): string
    {
        if ($buyPrice == 0) {
            return '0%';
        }

        return round(($profit * 100) / $buyPrice, 2).'%';
    }
}