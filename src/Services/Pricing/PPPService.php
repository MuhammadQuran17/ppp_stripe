<?php

namespace MuhammadUmar\PPPStripe\Services\Pricing;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use MuhammadUmar\PPPStripe\Services\Security\ProxyIpDetectionService;

/**
 * Calculate Price adjusted to each country - PPP
 */
final class PPPService
{
    public function __construct(
        private readonly ProxyIpDetectionService $proxyIpDetectionService,
        private readonly Request $request,
    ) {
    }

    public function getAdjustedPriceData()
    {
        $country_code_iso2 = null;
        $ip = $this->request->ip();

        $ppp_disabled = $this->proxyIpDetectionService->isProxy($ip);

        $originalPrice = config('subscription-plans.plans.lifetime.price_in_USA');
        $adjusted_price = $originalPrice;
        $country_name = 'United States';

        if ($ppp_disabled) {
            $country_code_iso2 = 'US';

            return compact('country_code_iso2', 'country_name', 'adjusted_price', 'ppp_disabled');
        }

        $country_code_iso2 = $this->getCountryCodeIso2($ip);

        if (!$country_code_iso2) $country_code_iso2 = 'US'; // default to United States

        $pppData = null;

        try {
            $country_code_iso3 = (new \League\ISO3166\ISO3166)->alpha2($country_code_iso2)['alpha3'];
            $country_name = (new \League\ISO3166\ISO3166)->alpha2($country_code_iso2)['name'];
            $adjusted_price = $this->convertPriceToPPP($originalPrice, $country_code_iso3);
        } catch (\Throwable $th) {
        }

        return compact('country_code_iso2', 'country_name', 'adjusted_price', 'ppp_disabled');
    }

    public function getCountryCodeIso2($ip): ?string
    {
        // First try using the 'CF-IPCountry' header from Cloudflare if present
        // @TODO retest with Cloudflare
        if ($this->request->hasHeader('CF-IPCountry')) {
            $country_code_iso2 = $this->request->header('CF-IPCountry');
        } else {
            $country_code_iso2 = rescue(function () use ($ip) {
                $response = Http::timeout(4)
                    ->get('http://ipwhois.app/json/' . urlencode($ip));

                return $response->successful()
                    ? $response->json('country_code')
                    : null;
            }, null, false);

            // flags will be here https://cdn.ipwhois.io/flags/uz.svg
        }

        return $country_code_iso2;
    }

    // convert Price to PPP get from table in db
    public function convertPriceToPPP(float $price, string $country_code_iso3): float
    {
        $pppData = DB::table('ppp_data')->where('country_code', $country_code_iso3)->first();

        if (!$pppData) return $price;

        if ($pppData->latest_ppp_value == 0) return $price;
        
        return $price * $pppData->latest_ppp_value;
    }
}
