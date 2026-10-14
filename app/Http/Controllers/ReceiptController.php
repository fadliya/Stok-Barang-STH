<?php

namespace App\Http\Controllers;

use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;

class ReceiptController extends Controller
{
    public function incoming(IncomingTransaction $transaction)
    {
        $transaction->load(['supplier', 'user', 'details.item']);

        return view('transactions.receipt', ['transaction' => $transaction, 'type' => 'incoming']);
    }

    public function outgoing(OutgoingTransaction $transaction)
    {
        $transaction->load(['user', 'details.item']);

        return view('transactions.receipt', ['transaction' => $transaction, 'type' => 'outgoing']);
    }
}
