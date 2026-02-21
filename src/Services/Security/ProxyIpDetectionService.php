<?php

namespace YourVendor\PPPStripe\Services\Security;

final class ProxyIpDetectionService
{
    public function isProxy(?string $ip): bool
    {
        try {
            if (!$ip) return false;

            $result = app(\Trustip\Trustip\ProxyCheck::class)->check($ip);

            if(data_get($result, 'status') == 'error') {
                logger()->error('ProxyIpDetectionService error: ' . data_get($result, 'message'));
                return false;
            }

            return (bool) data_get($result, 'data.is_proxy', false);
        } catch (\Throwable $e) {
            logger()->error('ProxyIpDetectionService error: ' . $e->getMessage() . '; CODE: ' . $e->getCode());
            return false;
        }
    }
}
