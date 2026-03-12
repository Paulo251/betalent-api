<?php

namespace App\Services\Gateways;

use Illuminate\Support\Facades\Http;

class Gateway2Service implements GatewayInterface
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config("services.gateway2.url");
    }

    public function charge(array $data): array
    {
        $response = Http::post("{$this->baseUrl}/transacoes", [
            "valor" => $data["amount"],
            "nome" => $data["name"],
            "email" => $data["email"],
            "numeroCartao" => $data["card_number"],
            "cvv" => $data["cvv"],
        ]);

        if ($response->failed()) {
            throw new \Exception("Gateway2: " . $response->body());
        }

        return $response->json();
    }

    public function refund(string $externalId): array
    {
        $response = Http::post("{$this->baseUrl}/transacoes/reembolso", [
            "id" => $externalId,
        ]);

        if ($response->failed()) {
            throw new \Exception("Gateway2: " . $response->body());
        }

        return $response->json();
    }
}
