# Namespace Customization Guide

This package uses the `MuhammadQuran\PPPStripe` namespace. To customize it for your organization:

## Step 1: Update Composer.json

Replace `muhammad-quran` and `MuhammadQuran` with your actual vendor name throughout the project:

```json
{
  "name": "muhammad-quran/ppp-stripe",
  "autoload": {
    "psr-4": {
      "MuhammadQuran\\PPPStripe\\": "src/"
    }
  }
}
```

Example with actual vendor:
```json
{
  "name": "acme/ppp-stripe",
  "autoload": {
    "psr-4": {
      "Acme\\PPPStripe\\": "src/"
    }
  }
}
```

## Step 2: Update All Namespaces in Source Files

All files in the `src/` directory should have their namespaces updated to match your vendor name:

### Files to Update:
- `src/PPPStripeServiceProvider.php`
- `src/Commands/ImportPPPData.php`
- `src/Config/subscription-plans.php`
- `src/Database/Migrations/2026_01_03_165456_create_ppp_data_table.php`
- `src/Http/Controllers/PurchaseController.php`
- `src/Services/Pricing/PPPService.php`
- `src/Services/Security/ProxyIpDetectionService.php`

### Example:
Change all occurrences of:
```php
namespace MuhammadQuran\PPPStripe\...
```

To:
```php
namespace Acme\PPPStripe\...
```

## Step 3: Update composer.json Extra Section

```json
"extra": {
  "laravel": {
    "providers": [
      "MuhammadQuran\\PPPStripe\\PPPStripeServiceProvider"
    ]
  }
}
```

To:
```json
"extra": {
  "laravel": {
    "providers": [
      "Acme\\PPPStripe\\PPPStripeServiceProvider"
    ]
  }
}
```

## Step 4: Update Usage Examples

When using this package in consuming applications, import from your custom namespace:

```php
use MuhammadQuran\PPPStripe\Services\Pricing\PPPService;
use MuhammadQuran\PPPStripe\Http\Controllers\PurchaseController;
```

Becomes:
```php
use Acme\PPPStripe\Services\Pricing\PPPService;
use Acme\PPPStripe\Http\Controllers\PurchaseController;
```

## Namespace Best Practices

- Use PascalCase for vendor names (e.g., `Acme`, `YourCompany`, `MyBrand`)
- Keep package names descriptive but concise
- Maintain PSR-4 compliance with directory structure matching namespace hierarchy
- Document custom namespaces in your package documentation

## Auto-Discovery

Laravel's package auto-discovery will automatically detect and register your service provider once you update the `extra.laravel.providers` section in `composer.json` with the correct namespace.
