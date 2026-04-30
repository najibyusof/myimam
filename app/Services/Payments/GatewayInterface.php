<?php

namespace App\Services\Payments;

use App\Models\Payment;

interface GatewayInterface
{
    public function createBill(Payment $payment, array $meta = []): array;

    public function parseCallback(array $payload): array;

    public function verifyCallback(array $payload): bool;
}
