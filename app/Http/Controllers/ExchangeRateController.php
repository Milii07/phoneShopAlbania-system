<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ExchangeRateService;
use Illuminate\Http\JsonResponse;

class ExchangeRateController extends Controller
{
    private $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    /**
     * Get current exchange rates
     */
    public function index(): JsonResponse
    {
        try {
            $rates = $this->exchangeRateService->getExchangeRates();

            return response()->json([
                'success' => true,
                'data' => $rates,
                'count' => count($rates),
                'message' => 'Exchange rates retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exchange rates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific currency exchange rate
     */
    public function show(string $currency): JsonResponse
    {
        try {
            $rates = $this->exchangeRateService->getExchangeRates();
            $currency = strtoupper($currency);

            if (!isset($rates[$currency])) {
                return response()->json([
                    'success' => false,
                    'message' => "Currency '{$currency}' not found"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $rates[$currency]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve exchange rate: ' . $e->getMessage()
            ], 500);
        }
    }
}
