<?php

namespace App\Services\Payments;

use App\Services\Payments\Gateways\BillplzGateway;
use App\Services\Payments\Gateways\ToyyibPayGateway;
use InvalidArgumentException;

class PaymentGatewayManager
{
    public function driver(?string $gateway = null): GatewayInterface
    {
        $gateway = strtolower($gateway ?: config('services.payment.default_gateway', 'billplz'));

        return match ($gateway) {
            'billplz' => app(BillplzGateway::class),
            'toyyibpay' => app(ToyyibPayGateway::class),
            default => throw new InvalidArgumentException("Unsupported payment gateway: {$gateway}"),
        };
    }
}
