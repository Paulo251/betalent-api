<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Transaction;
use App\Services\Gateways\GatewayManager;

class PurchaseService
{
    public function __construct(private GatewayManager $gatewayManager) {}

    public function execute(array $data): Transaction
    {
        $gateways = $this->gatewayManager->getActiveGateways();

        if (empty($gateways)) {
            throw new \Exception("Nenhum gateway disponível.");
        }

        $client = Client::firstOrCreate(
            ["email" => $data["email"]],
            ["name" => $data["name"]],
        );

        $lastError = null;

        foreach ($gateways as $gateway) {
            try {
                $service = $this->gatewayManager->resolve($gateway["name"]);
                $response = $service->charge($data);

                $transaction = Transaction::create([
                    "client_id" => $client->id,
                    "gateway_id" => $gateway["id"],
                    "external_id" => $response["id"],
                    "status" => "paid",
                    "amount" => $data["amount"],
                    "card_last_numbers" => substr($data["card_number"], -4),
                ]);

                return $transaction;
            } catch (\Exception $e) {
                $lastError = $e;
                continue;
            }
        }

        throw new \Exception(
            "Todos os gateways falharam: " . $lastError->getMessage(),
        );
    }
}
