# Purchasing Power Parity by country with Stripe or other payment gateway (Laravel & Cashier)

Detailed article is availabe at [https://docs.agenytics.com/blog/ppp-stripe](https://docs.agenytics.com/blog/ppp-stripe)

Implementing **Purchasing Power Parity (PPP) pricing per country** for a **Stripe Checkout** purchase flow. But you can use it for any other payment gateway that supports Laravel Cashier. This package uses [World Bank PPP data](https://data.worldbank.org/indicator/PA.NUS.PPPC.RF) and [TrustIP](https://trustip.io) to detect VPN/Proxy.

### Installation in Existing Laravel Project

1. **Install the package**
   ```bash
   composer require muhammad-umar/ppp-gateway
   ```

2. **Publish the assets**
   ```bash
   # PPP Data CSV
   php artisan vendor:publish --tag="ppp-gateway-data"
   ```

   Optional: You can also publish the config and migrations files:
   ```bash
   php artisan vendor:publish --tag="ppp-gateway-config"
   php artisan vendor:publish --tag="ppp-gateway-migrations"
   ```

3. **Run migrations**
   ```bash
   php artisan migrate
   ```

4. **Import PPP data**
   ```bash
   php artisan import:ppp
   ```

We have used lifetime product for demonstration purposes. You can use any other product you want.
5. **Configure environment variables**
   ```bash
   STRIPE_LIFETIME_PRODUCT_ID=prod_xxxxx
   TRUSTIP_API_KEY=your_trustip_api_key
   ```

6. **Configure subscription plans**

We have subscription-plans.php config file for payment products. You can configure it as you want. You should use this data to show price in frontend, sending PPPService->getAdjustedPriceData() parameter from your Controller. 

7. **Checkout page**

From fronted you should send a POST request to /purchase route (see PPPGatewayServiceProvider.php for route registration). It is binded to PurchaseController that will create a checkout session with adjusted price for the selected country.

## License

MIT