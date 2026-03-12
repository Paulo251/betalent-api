<?php

namespace App\Services\Gateways;

use App\Models\Gateway;

class GatewayManager
{
    private array $gatewayMap = [
        "Gateway1" => Gateway1Service::class,
        "Gateway2" => Gateway2Service::class,
    ];

    public function getActiveGateways(): array
    {
        return Gateway::where("is_active", true)
            ->orderBy("priority")
            ->get()
            ->toArray();
    }

    public function resolve(string $name): GatewayInterface
    {
        if (!isset($this->gatewayMap[$name])) {
            throw new \Exception("Gateway {$name} não encontrado.");
        }

        return new ($this->gatewayMap[$name])();
    }
}
