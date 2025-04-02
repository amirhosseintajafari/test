<?php

namespace App\Models\Repositories\Transactions;

use App\Models\Entities\Transaction;
use Illuminate\Support\Collection;

class MySqlTransactionRepository implements ITransactionRepository
{

    public function create(Transaction $transaction): Transaction
    {
        $id = Transaction::query()->insertGetId([
            'amount' => $transaction->amount,
            'order_id' => $transaction->order_id,
            'creator_id' => 1,
            'updated_at' => now(),
            'created_at' => now(),
        ]);
        $transaction->id = $id;
        return $transaction;
    }

    public function update(Transaction $transaction): Transaction
    {

        Transaction::query()->where('id',$transaction->id)->update([
            'status' => $transaction->status,
            'transaction_code' => $transaction->transaction_code,
            'gateway_name' => $transaction->gateway_name,
            'response_code' => $transaction->response_code,
            'updated_at' => now(),
        ]);
        return $transaction;
    }
}
