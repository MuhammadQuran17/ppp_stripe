# Purchasing Power Parity Pricing Package with Stripe (Cashier)

### Installation in Existing Laravel Project

1. **Install the package**
   ```bash
   composer require muhammad-umar/ppp-stripe
   ```

2. **Publish the assets**
   ```bash
   # PPP Data CSV
   php artisan vendor:publish --provider="MuhammadUmar\PPPStripe\PPPStripeServiceProvider" --tag="ppp-stripe-data"
   ```

   Optional: You can also publish the config and migrations files:
   ```bash
   php artisan vendor:publish --provider="MuhammadUmar\PPPStripe\PPPStripeServiceProvider" --tag="ppp-stripe-config"
   php artisan vendor:publish --provider="MuhammadUmar\PPPStripe\PPPStripeServiceProvider" --tag="ppp-stripe-migrations"
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

## License

MIT