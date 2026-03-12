<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post("/login", [AuthController::class, "login"]);
Route::post("/purchase", [PurchaseController::class, "store"]);

Route::middleware("auth:sanctum")->group(function () {
    Route::post("/logout", [AuthController::class, "logout"]);

    Route::get("/transactions", [TransactionController::class, "index"]);
    Route::get("/transactions/{transaction}", [
        TransactionController::class,
        "show",
    ]);
    Route::post("/transactions/{transaction}/refund", [
        TransactionController::class,
        "refund",
    ]);

    Route::get("/clients", [ClientController::class, "index"]);
    Route::get("/clients/{client}", [ClientController::class, "show"]);

    Route::patch("/gateways/{gateway}/toggle", [
        GatewayController::class,
        "toggle",
    ]);
    Route::patch("/gateways/{gateway}/priority", [
        GatewayController::class,
        "updatePriority",
    ]);

    Route::apiResource("/products", ProductController::class);

    Route::apiResource("/users", UserController::class);
});
