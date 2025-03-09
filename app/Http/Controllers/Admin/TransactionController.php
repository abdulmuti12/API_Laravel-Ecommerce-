<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\Transaction;
use App\Http\Controllers\Admin\BaseController;
use Tymon\JWTAuth\Facades\JWTAuth;

class TransactionController extends BaseController
{
    private $model;
    private $userId;

    Public $title="Transactions";

    public function __construct(Transaction $model)
    {
        $this->model = $model;
        $this->userId=JWTAuth::parseToken()->authenticate()->id;
    }
    public function transactionConfirmation($id){

        $data=$this->model->with('user','orders')->where('id', $id)->first();
        if(!$data){
            return $this->sendError('Data Not Valid', 'Data Not Valid');
        }

        $data->admin_id=$this->userId;
        $data->status='confirmed';
        $data->save();

        foreach ($data->orders as $order) {

            $product = Product::find($order->product_id);
            $product->stock = $product->stock - $order->qty;
            $product->save();

            $order->status = 'done';
            $order->save();
        }
        
        return $this->sendResponse($data, 'Success Confirmation Data');

    }
    public function transactionList(Request $request){

        $dataRequest=$request->all();
        $data = $this->model;

        if (isset($request->key)) {
            $data = $data->where('trx_order_code', 'like', '%' . $request->trx_order_code . '%');
        }

        if (isset($request->status)) {
            $data = $data->where('status', $request->status);
        }
        // $data=$data->where('admin_id', $this->userId);
        $data=$data->orderBy('id','desc');
        $data=$data->paginate(10)->appends($dataRequest);

        return $this->sendResponse($data, 'Success Load Data');
    }
    public function transactionDetail($id){

        $data=$this->model->with('user','orders')->where('id', $id)->first();

        if(!$data){
            return $this->sendError('Data Not Valid', 'Data Not Valid');
        }

        return $this->sendResponse($data, 'Success Load Data');
    }
}
