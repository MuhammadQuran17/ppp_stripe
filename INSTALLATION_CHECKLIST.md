# PPP Stripe Package - Structure & Configuration

## Complete Package Structure

```
ppp-stripe/
│
├── src/
│   ├── Commands/
│   │   └── ImportPPPData.php                    # Console command for CSV import
│   │
│   ├── Config/
│   │   └── subscription-plans.php               # Package configuration
│   │
│   ├── Database/
│   │   └── Migrations/
│   │       └── 2026_01_03_165456_create_ppp_data_table.php
│   │
│   ├── Http/
│   │   └── Controllers/
│   │       └── PurchaseController.php           # Stripe checkout controller
│   │
│   ├── Resources/
│   │   ├── ppp_world.csv                       # PPP data (published to storage)
│   │   └── README.md
│   │
│   ├── Services/
│   │   ├── Pricing/
│   │   │   └── PPPService.php                  # Core PPP calculation service
│   │   └── Security/
│   │       └── ProxyIpDetectionService.php     # Proxy/VPN detection
│   │
│   └── PPPStripeServiceProvider.php             # Service provider (auto-loaded)
│
├── tests/                                       # Unit & feature tests
│
├── composer.json                                # Package definition
├── phpunit.xml                                  # Testing configuration
├── pint.json                                    # Code style configuration
├── .gitignore                                   # Git ignore rules
├── LICENSE                                      # MIT License
├── README.md                                    # Main documentation
├── QUICKSTART.md                                # Quick start guide
├── NAMESPACE_GUIDE.md                           # Namespace customization
└── INSTALLATION_CHECKLIST.md                    # Setup checklist
```

## Namespace Mapping

All files use the `MuhammadQuran\PPPStripe` namespace structure. **Update this before publishing!**

```
File Path                                    → Namespace
────────────────────────────────────────────────────────────────────
src/PPPStripeServiceProvider.php            → MuhammadQuran\PPPStripe\PPPStripeServiceProvider
src/Commands/ImportPPPData.php              → MuhammadQuran\PPPStripe\Commands\ImportPPPData
src/Config/subscription-plans.php           → (config file, not a class)
src/Database/Migrations/...php              → (migration file)
src/Http/Controllers/PurchaseController.php → MuhammadQuran\PPPStripe\Http\Controllers\PurchaseController
src/Services/Pricing/PPPService.php         → MuhammadQuran\PPPStripe\Services\Pricing\PPPService
src/Services/Security/ProxyIpDetectionService.php → MuhammadQuran\PPPStripe\Services\Security\ProxyIpDetectionService
```

## PSR-4 Autoloading

Configured in `composer.json`:

```json
"autoload": {
  "psr-4": {
    "MuhammadQuran\\PPPStripe\\": "src/"
  }
},
"autoload-dev": {
  "psr-4": {
    "MuhammadQuran\\PPPStripe\\Tests\\": "tests/"
  }
}
```

## Service Provider Configuration

The ServiceProvider automatically:
1. Registers singleton services
2. Publishes migrations, configs, and data files
3. Loads migrations from the package
4. Registers console commands
5. Merges configuration

### Published Assets

| Tag | Published To | Usage |
|-----|--------------|-------|
| `ppp-stripe-migrations` | `database/migrations/` | Database tables |
| `ppp-stripe-config` | `config/subscription-plans.php` | Package configuration |
| `ppp-stripe-data` | `storage/app/private/ppp_world.csv` | PPP conversion data |

## Service Registration

The package registers these services automatically:

```php
app()->make('MuhammadQuran\PPPStripe\Services\Pricing\PPPService');
app()->make('MuhammadQuran\PPPStripe\Services\Security\ProxyIpDetectionService');
```

## How to Customize Vendor Name

**Before publishing to Composer:**

1. Search and replace `MuhammadQuran` → `YourActualVendor` in:
   - `composer.json`
   - `src/PPPStripeServiceProvider.php`
   - All files in `src/` directory

2. Update `composer.json` name:
   ```json
   "name": "muhammad-quran/ppp-stripe"
   ```

3. Update `composer.json` extra section:
   ```json
   "extra": {
     "laravel": {
       "providers": [
         "MuhammadQuran\\PPPStripe\\PPPStripeServiceProvider"
       ]
     }
   }
   ```

See `NAMESPACE_GUIDE.md` for detailed instructions.

## Installation Checklist

### For Package Developers
- [ ] Customize vendor/package names
- [ ] Update all namespace references
- [ ] Add author information to `composer.json`
- [ ] Update README with actual vendor details
- [ ] Add repository URL to `composer.json`
- [ ] Set up GitHub/GitLab repository
- [ ] Configure CI/CD pipelines

### For Package Consumers
- [ ] Run `composer require muhammad-quran/ppp-stripe`
- [ ] Run `php artisan vendor:publish --tag=ppp-stripe-config`
- [ ] Run `php artisan vendor:publish --tag=ppp-stripe-migrations`
- [ ] Run `php artisan vendor:publish --tag=ppp-stripe-data`
- [ ] Run `php artisan migrate`
- [ ] Add CSV file to `storage/app/private/ppp_world.csv`
- [ ] Run `php artisan import:ppp`
- [ ] Set `STRIPE_LIFETIME_PRODUCT_ID` in `.env`
- [ ] Update routes to use the package controllers

## Dependencies

### Required
- PHP ^8.2
- Laravel ^11.0
- Laravel Cashier ^15.0
- League ISO3166 ^4.3
- Trustip ^1.0

### Optional/Development
- PHPUnit ^11.0
- Laravel Pint ^1.0

## Quick Verification

Verify package structure:

```bash
# Check composer.json
composer validate

# List package files
composer show -t -l muhammad-quran/ppp-stripe

# Test autoloading
php artisan tinker
>>> class_exists('MuhammadQuran\PPPStripe\PPPStripeServiceProvider')
```

## Next Steps

1. **Publishing**: Register on Packagist.org
2. **CI/CD**: Set up automated testing
3. **Documentation**: Deploy to documentation site
4. **Versioning**: Use semantic versioning (semver)
5. **Changelog**: Maintain CHANGELOG.md

See `QUICKSTART.md` for development and usage examples.
