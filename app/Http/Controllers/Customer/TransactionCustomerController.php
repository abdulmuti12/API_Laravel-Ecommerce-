<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\Transaction;
use App\Http\Controllers\Customer\BaseController;
use Tymon\JWTAuth\Facades\JWTAuth;


class TransactionCustomerController extends BaseController
{
    private $model;
    private $userId;

    Public $title="Transactions";

    public function __construct(Transaction $model)
    {
        $this->model = $model;
        $this->userId=JWTAuth::parseToken()->authenticate()->id;
    }

    public function index(Request $request){

    }

    public function transactionProcess(Request $request){

        $data=$request->all();

        $times = date('YmdHis');
        $trxOrderCode = 'ORD'. '-' . $times;
        $totalPayment=0;
        foreach($data as $key => $value){

            $check=Order::with('product')->where('product_id', $value['product_id'])->where('user_id', $this->userId)->where('status', 'pending')->first();

            if(!$check){
                return $this->sendError('Data Not Valid', 'Data Not Valid');
            }

            $check->status = 'process';
            $check->trx_order_code = $trxOrderCode;
            $check->unit_price=$check->product->price;
            $check->total_price = $check->product->price * $check->qty;
            $check->updated_at = date('Y-m-d H:i:s');
            $check->save();

            $totalPayment += $check->product->price * $check->qty;
        }

        $transaction = Transaction::create([
            'trx_order_code' => $trxOrderCode,
            'total_transaction' => $totalPayment,
            'payment_method' => 'check_payment_by_admin',
            'user_id' => $this->userId
        ]);

        return $this->sendResponse($transaction, 'Success Transaction Process');
    }

}
