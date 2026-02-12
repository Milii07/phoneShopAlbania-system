<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Carbon\Carbon;

class ExchangeRateService
{
    private $cacheFile;
    private $cacheDirectory;

    public function __construct()
    {
        $this->cacheDirectory = storage_path('app/exchange-rates');
        $this->ensureCacheDirectoryExists();
    }

    /**
     * Ensure cache directory exists
     */
    private function ensureCacheDirectoryExists(): void
    {
        if (!is_dir($this->cacheDirectory)) {
            mkdir($this->cacheDirectory, 0755, true);
        }
    }

    /**
     * Get cache file path for today
     */
    private function getCacheFilePath(): string
    {
        return $this->cacheDirectory . '/' . Carbon::now()->format('Y-m-d') . '.json';
    }

    /**
     * Check if cache exists for today
     */
    private function cacheExistsForToday(): bool
    {
        return file_exists($this->getCacheFilePath());
    }

    /**
     * Get cached exchange rates
     */
    private function getCachedRates(): array
    {
        $cacheFile = $this->getCacheFilePath();
        if (file_exists($cacheFile)) {
            $content = file_get_contents($cacheFile);
            return json_decode($content, true) ?? [];
        }
        return [];
    }

    /**
     * Save exchange rates to cache
     */
    private function saveRatesToCache(array $rates): void
    {
        $cacheFile = $this->getCacheFilePath();
        file_put_contents($cacheFile, json_encode($rates, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Fetch exchange rates from Iliria API
     */
    public function getExchangeRates(): array
    {
        // Check if cache exists for today
        if ($this->cacheExistsForToday()) {
            return $this->getCachedRates();
        }

        try {
            // Fetch HTML from Iliria
            $response = Http::timeout(30)->get(config('services.iliria.url'));

            // Some tooling/static-analysis or alternate handlers may surface a Guzzle Promise;
            // if so, resolve it and wrap the PSR response into an Illuminate Response.
            if ($response instanceof \GuzzleHttp\Promise\PromiseInterface) {
                try {
                    $psrResponse = $response->wait();
                    $response = new \Illuminate\Http\Client\Response($psrResponse);
                } catch (\Throwable $e) {
                    $cached = $this->getCachedRates();
                    if (!empty($cached)) {
                        return $cached;
                    }
                    throw $e;
                }
            }

            // Ensure we have an Illuminate response and that the request succeeded
            if (!($response instanceof \Illuminate\Http\Client\Response) || !$response->successful()) {
                // Return cached data if available, even if it's from a previous date
                $cached = $this->getCachedRates();
                if (!empty($cached)) {
                    return $cached;
                }
                throw new \Exception('Failed to fetch exchange rates from Iliria');
            }

            $html = $response->body();
            $rates = $this->parseExchangeRates($html);

            // Save to cache
            $this->saveRatesToCache($rates);

            return $rates;
        } catch (\Exception $e) {
            // Return cached data if available
            $cached = $this->getCachedRates();
            if (!empty($cached)) {
                return $cached;
            }

            throw $e;
        }
    }

    /**
     * Parse HTML and extract exchange rates
     */
    private function parseExchangeRates(string $html): array
    {
        $crawler = new Crawler($html);
        $rates = [];


        $crawler->filter('div.line[rate]')->each(function (Crawler $node) use (&$rates) {
            try {
                $rateAttribute = $node->attr('rate');

                $currencyElement = $node->filter('span b');
                if ($currencyElement->count() === 0) {
                    return;
                }
                $currency = trim($currencyElement->text());

                $spans = $node->filter('span');

                if ($spans->count() < 3) {
                    return;
                }

                $buyText = trim($spans->eq(1)->text());
                $buyRate = $this->extractPrice($buyText);

                $sellText = trim($spans->eq(2)->text());
                $sellRate = $this->extractPrice($sellText);

                if ($buyRate !== null && $sellRate !== null) {
                    $rates[$currency] = [
                        'currency' => $currency,
                        'buy' => $buyRate,
                        'sell' => $sellRate,
                        'date' => Carbon::now()->format('Y-m-d'),
                        'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    ];
                }
            } catch (\Exception $e) {
                return;
            }
        });

        return $rates;
    }

    /**
     * Extract numeric price from text that may contain symbols
     */
    private function extractPrice(string $text): ?float
    {

        $cleaned = preg_replace('/[^0-9.]/', '', $text);

        if (empty($cleaned)) {
            return null;
        }

        $price = (float) $cleaned;
        return $price > 0 ? $price : null;
    }
}
