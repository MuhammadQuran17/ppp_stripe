# Purchasing Power Parity Pricing Package with Stripe (Cashier)

A Laravel package that integrates Purchasing Power Parity (PPP) pricing adjustments with Stripe for more equitable fair global pricing based on country-specific economic factors.

## Features

- Automatic PPP price adjustments based on user's country
- IP-based country detection with Cloudflare support
- Proxy/VPN detection to prevent PPP abuse
- Stripe integration using Laravel Cashier
- Database-driven PPP conversion rates
- Console command for importing PPP data from CSV
- Easy configuration and customization

## Requirements

- PHP 8.2+
- Laravel 11.0+
- Laravel Cashier 15.0+
- League ISO3166 library
- Trustip proxy detection service

## Installation

### Step 1: Install via Composer

```bash
composer require muhammad-umar/ppp-stripe
```

### Step 2: Publish Assets

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ppp-stripe-config
```

Publish the migrations:

```bash
php artisan vendor:publish --tag=ppp-stripe-migrations
```

Publish the PPP data:

```bash
php artisan vendor:publish --tag=ppp-stripe-data
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

### Step 4: Import PPP Data

Ensure your `storage/app/private/ppp_world.csv` file is in place, then run:

```bash
php artisan import:ppp
```

## Configuration

The package comes with a `config/subscription-plans.php` configuration file. Update it with your Stripe product IDs and pricing:

```php
return [
    'plans' => [
        'lifetime' => [
            'price_in_USA' => 98,
            'stripe_product_id' => env('STRIPE_LIFETIME_PRODUCT_ID'),
        ],
    ],
];
```

## Usage

### In Your Controller which used to show the price to the user

```php
<?php

namespace App\Http\Controllers;

use MuhammadUmar\PPPStripe\Services\Pricing\PPPService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request, PPPService $pppService)
    {
        // Get adjusted price based on user's country
        $priceData = $pppService->getAdjustedPriceData();
        
        // $priceData will contain:
        // - country_code_iso2: User's country code
        // - country_name: User's country name
        // - adjusted_price: Price adjusted by PPP factor
        // - ppp_disabled: Whether PPP is disabled (proxy detected)

        // Render frontend view with the price data
        return view('home', [
            'priceData' => $priceData,
        ]);
    }
}
```

### PurchaseController 

The package includes a ready-to-use `PurchaseController` that handles the Stripe checkout flow

From frontend, when user click on the buy button, you should send the request to this controller to create the checkout session.

```html
<a href="{{ route('checkout') }}"
    class="flex items-center gap-2 text-sm px-6 py-2.5 border border-indigo-400 hover:bg-indigo-300/10 active:scale-95 transition rounded-full mt-6">
    Purchase
</a>
```

```php
// In your routes/api.php
Route::post('/checkout', MuhammadUmar\PPPStripe\Http\Controllers\PurchaseController::class)
    ->middleware('auth');
```

### Service Class

#### PPPService

Handles PPP price calculations and country detection.

**Methods:**

- `getAdjustedPriceData()`: Returns adjusted price data array
- `getCountryCodeIso2($ip)`: Detects country code from IP address
- `convertPriceToPPP($price, $country_code_iso3)`: Converts price using PPP factors

#### ProxyIpDetectionService

Detects if a user is connecting through a proxy or VPN.

**Methods:**

- `isProxy(?string $ip): bool`: Returns true if IP is detected as proxy/VPN

## Database

The package creates a `ppp_data` table with the following structure:

```
- id (primary key)
- country_name (string)
- country_code (string, unique)
- latest_ppp_value (decimal)
- created_at
- updated_at
```

## Console Commands

### import:ppp

Imports PPP data from the CSV file into the database.

```bash
php artisan import:ppp
```

This command:
- Reads the CSV file from `storage/app/private/ppp_world.csv`
- Parses country names and codes
- Extracts the latest available PPP value
- Inserts or updates records in the database
- Runs within a database transaction for data integrity

## Customization

### Publishing Package Views (if needed)

To publish and customize views, add them to the service provider's `boot()` method.

### Extending Services

You can extend the services by creating your own classes that extend the package services:

```php
<?php

namespace App\Services;

use MuhammadUmar\PPPStripe\Services\Pricing\PPPService as BaseService;

class CustomPPPService extends BaseService
{
    // Override methods as needed
}
```

## Environment Variables

Make sure your `.env` file includes:

```
STRIPE_LIFETIME_PRODUCT_ID=prod_xxxxx
```

## Architecture

```
src/
├── Commands/
│   └── ImportPPPData.php
├── Config/
│   └── subscription-plans.php
├── Database/
│   └── Migrations/
│       └── 2026_01_03_165456_create_ppp_data_table.php
├── Http/
│   └── Controllers/
│       └── PurchaseController.php
├── Services/
│   ├── Pricing/
│   │   └── PPPService.php
│   └── Security/
│       └── ProxyIpDetectionService.php
├── Resources/
│   └── ppp_world.csv
└── PPPStripeServiceProvider.php
```

## How It Works

1. **User initiates checkout** → Request hits your checkout endpoint
2. **IP Detection** → Package detects user's IP and country
3. **Proxy Check** → Verifies if user is on proxy/VPN (uses Trustip)
4. **Country Lookup** → Maps country code to ISO3 format
5. **PPP Lookup** → Retrieves PPP conversion factor from database
6. **Price Adjustment** → Multiplies base price by PPP factor
7. **Stripe Checkout** → Initiates checkout with adjusted price

## Troubleshooting

### CSV File Not Found

Ensure you've run `php artisan vendor:publish --tag=ppp-stripe-data` and that the CSV file exists at `storage/app/private/ppp_world.csv`.

### IP Detection Not Working

Make sure your application is properly configured to read the user's real IP address (especially important behind proxies/load balancers). Configure this in `config/trustedproxy.php`.

### Database Connection Issues

Ensure the `ppp_data` table has been created by running migrations.

## Security Considerations

- The package uses Trustip service to detect and block PPP pricing for proxy/VPN users
- Always validate IP addresses on the server side
- Store Stripe product IDs in environment variables
- Consider rate limiting the checkout endpoint

## License

MIT

## Support

For issues, questions, or contributions, please visit the repository or contact your vendor.

## Changelog

### v1.0.0 (2026-02-21)
- Initial release
- PPP price adjustment
- Proxy detection
- Stripe integration
- CSV import command
