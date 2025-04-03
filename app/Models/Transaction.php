<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public $guarded=[];
    /**
     * @var int|mixed
     */
    public mixed $creator_id;
    public mixed $order_id;
    public mixed $amount;
    /**
     * @var mixed|string
     */
    public mixed $status;
    public mixed $transaction_code;
    public mixed $gateway_name;
    public mixed $response_code;
}
