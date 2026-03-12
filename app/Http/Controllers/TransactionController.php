<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\Gateways\GatewayManager;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private GatewayManager $gatewayManager) {}

    public function index()
    {
        $transactions = Transaction::with(["client", "gateway"])->get();

        return response()->json($transactions);
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(["client", "gateway"]);

        return response()->json($transaction);
    }

    public function refund(Transaction $transaction)
    {
        if ($transaction->status === "voided") {
            return response()->json(
                ["message" => "Transação já reembolsada."],
                422,
            );
        }

        try {
            $service = $this->gatewayManager->resolve(
                $transaction->gateway->name,
            );
            $service->refund($transaction->external_id);

            $transaction->update(["status" => "voided"]);

            return response()->json([
                "message" => "Reembolso realizado com sucesso.",
            ]);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 422);
        }
    }
}
