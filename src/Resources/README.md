# PPP Stripe CSV Data

This file should be populated with the PPP world data CSV.
The package will publish this to `storage/app/private/ppp_world.csv` when you run:

```bash
php artisan vendor:publish --tag=ppp-gateway-data
```

Then run the import command to import the data into the database:

```bash
php artisan import:ppp
```
