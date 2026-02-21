# Quick Start Guide for PPP Stripe Package

## For Package Developers/Maintainers

### Setting Up Development Environment

1. **Clone the repository**
   ```bash
   git clone https://github.com/muhammad-quran/ppp-stripe.git
   cd ppp-stripe
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Create a test Laravel application** (if needed)
   ```bash
   composer create-project laravel/laravel test-app
   cd test-app
   ```

4. **Install the package in development mode**
   ```bash
   composer require --dev ../ppp-stripe
   ```

## For Package Consumers

### Installation in Existing Laravel Project

1. **Install the package**
   ```bash
   composer require muhammad-quran/ppp-stripe
   ```

2. **Publish the assets**
   ```bash
   # Configuration
   php artisan vendor:publish --provider="MuhammadQuran\PPPStripe\PPPStripeServiceProvider" --tag="ppp-stripe-config"
   
   # Migrations
   php artisan vendor:publish --provider="MuhammadQuran\PPPStripe\PPPStripeServiceProvider" --tag="ppp-stripe-migrations"
   
   # PPP Data CSV
   php artisan vendor:publish --provider="MuhammadQuran\PPPStripe\PPPStripeServiceProvider" --tag="ppp-stripe-data"
   ```

3. **Run migrations**
   ```bash
   php artisan migrate
   ```

4. **Import PPP data**
   ```bash
   php artisan import:ppp
   ```

5. **Configure environment variables**
   ```bash
   STRIPE_LIFETIME_PRODUCT_ID=prod_xxxxx
   ```

### Basic Usage Example

Create a checkout controller in your application:

```php
<?php

namespace App\Http\Controllers;

use MuhammadQuran\PPPStripe\Services\Pricing\PPPService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function checkout(Request $request, PPPService $pppService)
    {
        // Get PPP-adjusted pricing
        $priceData = $pppService->getAdjustedPriceData();

        return response()->json([
            'country' => $priceData['country_name'],
            'country_code' => $priceData['country_code_iso2'],
            'original_price' => config('subscription-plans.plans.lifetime.price_in_USA'),
            'adjusted_price' => $priceData['adjusted_price'],
            'ppp_applied' => !$priceData['ppp_disabled'],
        ]);
    }

    public function purchase(Request $request)
    {
        // Use the PurchaseController or implement custom logic
        return app(MuhammadQuran\PPPStripe\Http\Controllers\PurchaseController::class)($request);
    }
}
```

### Register Routes

In `routes/api.php`:

```php
use App\Http\Controllers\SubscriptionController;
use MuhammadQuran\PPPStripe\Http\Controllers\PurchaseController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pricing', [SubscriptionController::class, 'checkout']);
    Route::post('/subscribe', [SubscriptionController::class, 'purchase']);
});
```

## Package Structure

```
ppp-stripe/
├── src/
│   ├── Commands/              # Artisan commands
│   ├── Config/                # Configuration files
│   ├── Database/
│   │   └── Migrations/        # Database migrations
│   ├── Http/
│   │   └── Controllers/       # HTTP controllers
│   ├── Resources/             # Data files (CSV, etc.)
│   ├── Services/
│   │   ├── Pricing/          # PPP pricing service
│   │   └── Security/         # Security services
│   └── PPPStripeServiceProvider.php
├── tests/                     # Unit tests
├── README.md                  # Documentation
├── composer.json              # Package definition
└── LICENSE                    # MIT License
```

## Testing

Run tests with:

```bash
./vendor/bin/phpunit
```

Or with coverage:

```bash
./vendor/bin/phpunit --coverage-html coverage
```

## Code Standards

Use Laravel Pint for code formatting:

```bash
./vendor/bin/pint
```

## Debugging Tips

### Check if service is registered
```php
dd(app('MuhammadQuran\PPPStripe\Services\Pricing\PPPService'));
```

### View all config
```php
dd(config('subscription-plans'));
```

### Check PPP data in database
```php
php artisan tinker
>>> DB::table('ppp_data')->first()
>>> DB::table('ppp_data')->where('country_code', 'US')->first()
```

### Test IP detection
```php
php artisan tinker
>>> app('MuhammadQuran\PPPStripe\Services\Security\ProxyIpDetectionService')->isProxy('8.8.8.8')
```

## Common Issues & Solutions

### Issue: "Service provider not found"
**Solution:** Make sure `composer.json` has the correct namespace in `extra.laravel.providers`

### Issue: "ppp_data table not found"
**Solution:** Run migrations: `php artisan migrate`

### Issue: "CSV file not found" when running import
**Solution:** Verify file exists at `storage/app/private/ppp_world.csv`

### Issue: "Country code not detected"
**Solution:** Check your application's IP detection configuration and Cloudflare headers if behind a proxy

## Contributing

1. Create a feature branch
2. Make your changes
3. Run tests and linting
4. Submit a pull request

## Support & Documentation

- Full documentation: See `README.md`
- Namespace customization: See `NAMESPACE_GUIDE.md`
- Issues: Open an issue on the repository
