<?php

namespace App\Services\Gateways;

interface GatewayInterface
{
    public function charge(array $data): array;
    public function refound(string $externalId): array;
}
