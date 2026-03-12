<?php

namespace App\Http\Controllers;

use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(private PurchaseService $purchaseService) {}

    public function store(Request $request)
    {
        $request->validate([
            "amount" => "required|integer|min:1",
            "name" => "required|string",
            "email" => "required|email",
            "card_number" => "required|string|size:16",
            "cvv" => "required|string",
        ]);

        try {
            $transaction = $this->purchaseService->execute($request->all());

            return response()->json($transaction, 201);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 422);
        }
    }
}
