<?php

namespace App\Http\Controllers;

use App\Models\Client;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::all();

        return response()->json($clients);
    }

    public function show(Client $client)
    {
        $client->load("transactions.gateway");

        return response()->json($client);
    }
}
