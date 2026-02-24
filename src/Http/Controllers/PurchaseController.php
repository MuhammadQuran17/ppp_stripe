<?php

namespace MuhammadUmar\PPPGateway\Http\Controllers;

use MuhammadUmar\PPPGateway\Services\Pricing\PPPService;
use Illuminate\Http\Request;
use Laravel\Cashier\Concerns\ManagesCustomer;

class PurchaseController
{
    use ManagesCustomer;

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, PPPService $PPPService)
    {
        ['adjusted_price' => $adjusted_price] = $PPPService->getAdjustedPriceData($request);

        return $request->user()->checkout([[
            'price_data' => [
                'currency' => $this->preferredCurrency(),
                'product' => config('subscription-plans.plans.lifetime.stripe_product_id'),
                'unit_amount_decimal' => round($adjusted_price, 2) * 100,
            ],
            'quantity' => 1,
        ]], [
            'success_url' => route('home', ['success' => '1']),
            'cancel_url' => route('home', ['success' => '0']),
            'allow_promotion_codes' => true,
        ]);
    }
}
