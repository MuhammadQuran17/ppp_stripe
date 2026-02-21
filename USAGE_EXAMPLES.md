# Usage Examples - PPP Stripe Package

## Basic Implementation

### Example 1: Simple Pricing Display

```php
<?php

namespace App\Http\Controllers;

use YourVendor\PPPStripe\Services\Pricing\PPPService;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function show(PPPService $pppService)
    {
        $priceData = $pppService->getAdjustedPriceData();
        
        return view('pricing', [
            'original_price' => 98,
            'adjusted_price' => round($priceData['adjusted_price'], 2),
            'country' => $priceData['country_name'],
            'country_code' => $priceData['country_code_iso2'],
            'is_proxy' => $priceData['ppp_disabled'],
        ]);
    }
}
```

### Example 2: JSON API Response

```php
<?php

namespace App\Http\Controllers\Api;

use YourVendor\PPPStripe\Services\Pricing\PPPService;
use Illuminate\Http\JsonResponse;

class PricingController
{
    public function getPricing(PPPService $pppService): JsonResponse
    {
        $priceData = $pppService->getAdjustedPriceData();
        $basePrice = config('subscription-plans.plans.lifetime.price_in_USA');
        
        return response()->json([
            'success' => true,
            'data' => [
                'base_price' => $basePrice,
                'adjusted_price' => round($priceData['adjusted_price'], 2),
                'discount_percentage' => round(
                    ((($basePrice - $priceData['adjusted_price']) / $basePrice) * 100),
                    2
                ),
                'country' => $priceData['country_name'],
                'country_code' => $priceData['country_code_iso2'],
                'ppp_applied' => !$priceData['ppp_disabled'],
                'currency' => 'USD',
            ],
        ]);
    }
}
```

### Example 3: Complete Checkout Flow

```php
<?php

namespace App\Http\Controllers;

use YourVendor\PPPStripe\Services\Pricing\PPPService;
use YourVendor\PPPStripe\Services\Security\ProxyIpDetectionService;
use Illuminate\Http\Request;

class CheckoutController
{
    public function checkout(Request $request, PPPService $pppService)
    {
        // Get price data
        $priceData = $pppService->getAdjustedPriceData();
        
        // Get user
        $user = $request->user();
        
        // Calculate final price
        $basePrice = config('subscription-plans.plans.lifetime.price_in_USA');
        $adjustedPrice = round($priceData['adjusted_price'], 2);
        
        // Create checkout session
        $checkout = $user->checkout([
            [
                'price_data' => [
                    'currency' => 'USD',
                    'product' => config('subscription-plans.plans.lifetime.stripe_product_id'),
                    'unit_amount_decimal' => $adjustedPrice * 100,
                ],
                'quantity' => 1,
            ]
        ], [
            'success_url' => route('success', ['session_id' => '{CHECKOUT_SESSION_ID}']),
            'cancel_url' => route('checkout'),
            'allow_promotion_codes' => true,
            'metadata' => [
                'user_country' => $priceData['country_code_iso2'],
                'country_name' => $priceData['country_name'],
                'ppp_applied' => !$priceData['ppp_disabled'],
                'original_price' => $basePrice,
            ],
        ]);
        
        return view('checkout.confirm', [
            'checkout_url' => $checkout->url,
            'price_data' => [
                'original' => $basePrice,
                'adjusted' => $adjustedPrice,
                'country' => $priceData['country_name'],
            ],
        ]);
    }
    
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        
        // Retrieve Stripe session details
        $session = auth()->user()->invoices()->findByPaymentIntent($sessionId);
        
        return view('checkout.success', [
            'session' => $session,
        ]);
    }
}
```

### Example 4: Proxy Detection

```php
<?php

namespace App\Http\Controllers;

use YourVendor\PPPStripe\Services\Security\ProxyIpDetectionService;
use Illuminate\Http\Request;

class SecurityController
{
    public function checkUser(Request $request, ProxyIpDetectionService $proxyService)
    {
        $ip = $request->ip();
        $isProxy = $proxyService->isProxy($ip);
        
        if ($isProxy) {
            // User is on proxy/VPN - might want to:
            // - Disable PPP pricing
            // - Log the attempt
            // - Show warning
            // - Require additional verification
            
            return response()->json([
                'message' => 'Proxy/VPN detected',
                'ppp_available' => false,
            ]);
        }
        
        return response()->json([
            'message' => 'IP verified',
            'ppp_available' => true,
        ]);
    }
}
```

### Example 5: Logging & Analytics

```php
<?php

namespace App\Http\Controllers;

use YourVendor\PPPStripe\Services\Pricing\PPPService;
use App\Models\PricingLog;
use Illuminate\Http\Request;

class AnalyticsController
{
    public function logPricing(Request $request, PPPService $pppService)
    {
        $priceData = $pppService->getAdjustedPriceData();
        $basePrice = config('subscription-plans.plans.lifetime.price_in_USA');
        
        // Log pricing data
        PricingLog::create([
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'country_code' => $priceData['country_code_iso2'],
            'country_name' => $priceData['country_name'],
            'base_price' => $basePrice,
            'adjusted_price' => $priceData['adjusted_price'],
            'discount_applied' => $basePrice - $priceData['adjusted_price'],
            'ppp_applied' => !$priceData['ppp_disabled'],
        ]);
        
        return response()->json(['logged' => true]);
    }
}
```

## Routes Configuration

```php
<?php

// routes/web.php
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PricingController;

Route::middleware('auth')->group(function () {
    Route::get('/pricing', [PricingController::class, 'show'])->name('pricing');
    Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('success');
});

// routes/api.php
use App\Http\Controllers\Api\PricingController;

Route::middleware('api')->group(function () {
    Route::get('/pricing', [PricingController::class, 'getPricing']);
    Route::get('/pricing/check', [PricingController::class, 'checkUser']);
});
```

## Blade Template Example

```blade
<!-- resources/views/pricing.blade.php -->
@extends('layouts.app')

@section('content')
<div class="pricing-container">
    <h1>Subscribe Now</h1>
    
    @if(!$is_proxy)
        <div class="ppp-notice">
            <p>💰 We detect you're from <strong>{{ $country }}</strong></p>
            <p>Your price has been adjusted for your region</p>
        </div>
    @endif
    
    <div class="pricing-box">
        @if($original_price != $adjusted_price)
            <div class="original-price">
                <span class="label">Original price:</span>
                <s>${{ number_format($original_price, 2) }}</s>
            </div>
        @endif
        
        <div class="adjusted-price">
            <span class="label">Your price:</span>
            <h2>${{ number_format($adjusted_price, 2) }}</h2>
        </div>
        
        <form action="{{ route('checkout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">
                Complete Purchase
            </button>
        </form>
    </div>
    
    @if($is_proxy)
        <div class="alert alert-info">
            <p>Your IP has been detected as a proxy/VPN.</p>
            <p>PPP pricing is not available for this connection.</p>
        </div>
    @endif
</div>
@endsection
```

## Advanced: Custom Service Extension

```php
<?php

namespace App\Services;

use YourVendor\PPPStripe\Services\Pricing\PPPService as BasePPPService;

class CustomPPPService extends BasePPPService
{
    /**
     * Override to add custom logic
     */
    public function getAdjustedPriceData()
    {
        $data = parent::getAdjustedPriceData();
        
        // Custom: Add volumetric discounts
        $data['volume_discount'] = $this->calculateVolumeDiscount($data);
        
        // Custom: Add loyalty discount
        $data['loyalty_discount'] = $this->calculateLoyaltyDiscount();
        
        // Custom: Apply all discounts
        $totalDiscount = $data['volume_discount'] + $data['loyalty_discount'];
        $data['final_price'] = $data['adjusted_price'] - $totalDiscount;
        
        return $data;
    }
    
    private function calculateVolumeDiscount($data): float
    {
        // Implement your volume discount logic
        return 0;
    }
    
    private function calculateLoyaltyDiscount(): float
    {
        // Implement your loyalty logic
        $user = auth()->user();
        
        if ($user->has_previous_purchase) {
            return 5; // $5 discount
        }
        
        return 0;
    }
}
```

Register in service provider:

```php
// In your app's service provider
public function register()
{
    $this->app->singleton(
        'YourVendor\PPPStripe\Services\Pricing\PPPService',
        'App\Services\CustomPPPService'
    );
}
```

## Testing

```php
<?php

namespace Tests\Feature;

use YourVendor\PPPStripe\Services\Pricing\PPPService;
use Tests\TestCase;

class PricingTest extends TestCase
{
    public function test_pricing_api_returns_adjusted_price()
    {
        $response = $this->getJson('/api/pricing');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'base_price',
                    'adjusted_price',
                    'country',
                    'country_code',
                    'ppp_applied',
                ]
            ]);
    }
    
    public function test_ppp_service_calculates_price()
    {
        $service = app(PPPService::class);
        $data = $service->getAdjustedPriceData();
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('adjusted_price', $data);
        $this->assertArrayHasKey('country_code_iso2', $data);
    }
}
```

---

These examples show the flexibility and power of the PPP Stripe package! 🚀
