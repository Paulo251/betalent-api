<?php

namespace App\Services\Gateways;

use Illuminate\Support\Facades\Http;

class Gateway1Service implements GatewayInterface
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config("services.gateway1.url");
    }

    public function charge(array $data): array
    {
        $response = Http::post("{$this->baseUrl}/transactions", [
            "amount" => $data["amount"],
            "name" => $data["name"],
            "email" => $data["email"],
            "cardNumber" => $data["card_number"],
            "cvv" => $data["cvv"],
        ]);

        if ($response->failed()) {
            throw new \Exception("Gateway1: " . $response->body());
        }

        return $response->json();
    }

    public function refund(string $externalId): array
    {
        $response = Http::post(
            "{$this->baseUrl}/transactions/{$externalId}/charge_back",
        );

        if ($response->failed()) {
            throw new \Exception("Gateway1: " . $response->body());
        }

        return $response->json();
    }
}
