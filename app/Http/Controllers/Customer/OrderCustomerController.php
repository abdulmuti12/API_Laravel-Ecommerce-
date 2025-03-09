<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Http\Controllers\Customer\BaseController;
use Tymon\JWTAuth\Facades\JWTAuth;



class OrderCustomerController extends BaseController
{
    private $model;
    private $userId;


    Public $title="Order";

    public function __construct(Order $model)
    {
        $this->model = $model;
        $this->userId=JWTAuth::parseToken()->authenticate()->id;
    }

    public function checkoutDetail(Request $request){

        $data=$this->model->with('product')->where('trx_order_code', $request->trx_order_code)
            ->where('user_id', $this->userId)
            ->where('status', $request->status)->get();

        // dd(count($data));    
        $dataresult=[];

        $priceTotal=0;
        foreach($data as $value){
            $dataresult[] =[
                'id' => $value->id,
                'product_id' => $value->product_id,
                'name' => $value->product->name,
                'qty' => $value->qty,
                'price' => $value->product->price,
                'total' => $value->product->price * $value->qty
            ];
            $priceTotal += $value->product->price * $value->qty;
        }

        $collectionData[] =[
            'detail' => $dataresult,
            'total_payment' => $priceTotal,
        ];

        return $this->sendResponse($collectionData, 'Success Load Data');
    }

    public function countBracket(Request $request){

        $data=$this->model->with('product')->where('trx_order_code', 'ORD')
        ->where('user_id', $this->userId)
        ->where('status', 'pending')->get();

        return $this->sendResponse(count($data), 'Success Load Data');
    }

    public function updateCheckout(Request $request){

        $data=$request->all();

        foreach($data as $key => $value){

            $product = Product::where('id', $value['product_id'])->first();

            if($product->stock < $value['qty']){
                return $this->sendError('Stock Not Enough', $product->name.' Stock Not Enough');
            }


            $check=Order::where('product_id', $value['product_id'])->where('user_id', $this->userId)->where('status', 'pending')->first();
            if($value['qty'] == 0){
                $check->delete();
            }else{
                $check->qty = $value['qty'];
                $check->save();
            }
        }

        return $this->sendResponse('', 'Success Update Data');
    }

    public function deleteCheckout(Request $request){

        $data=$request->all();

        foreach($data as $key => $value){

            $check=Order::where('product_id', $value['product_id'])->where('user_id', $this->userId)->where('status', 'pending')->first();

            if(!$check){
                return $this->sendError('Data Not Valid', 'Data Not Valid');
            }

            $check->delete();
        }

        return $this->sendResponse('', 'Success Delete Data');
    }

    public function index(Request $request){

        $dataRequest=$request->all();
        $data = $this->model;
        $userId = JWTAuth::parseToken()->authenticate()->id;
        $data = Order::join('products', 'orders.product_id', '=', 'products.id')->selectRaw('SUM(products.price) as total, SUM(orders.qty) as total_item, orders.status, orders.user_id,trx_order_code');

        if (isset($request->status)) {
            $data = $data->where('status', $request->status);
        }
        $data=$data->where('user_id', $userId)->groupBy('status', 'user_id','trx_order_code');
        $data=$data->paginate(10)->appends($dataRequest);

        return $this->sendResponse($data, 'Success Load Data');

    }

    public function proccessOrder(Request $request){


        if(count($request->all()) == 0){
            return $this->sendError('No Data', 'No Process Order Data');
        }

        #process check stock order
        $dataOrder=[];
        foreach($request->all() as $key => $value){
            $product = Product::where('id', $value['product_id'])->first();

            if(!$product){
                return $this->sendError('Id Not Found', "Id ".$value['product_id'].' Not Found');
            }
            if($product->stock < $value['qty']){
                return $this->sendError('Stock Not Enough', $product->name.' Stock Not Enough');
            }

            $dataOrder[] = [
                'product_id' => $value['product_id'],
                'qty' => $value['qty'],
                'price' => $product->price,
                'total' => $product->price * $value['qty'],
                'user_id' => $this->userId
            ];
        }

        $trx='ORD';
        foreach($dataOrder as $key => $value){
            // $product = Product::find($value['product_id']);
            // $product->stock = $product->stock - $value['qty'];
            // $product->save();

            $check=Order::where('product_id', $value['product_id'])->where('user_id', $this->userId)->where('status', 'pending')->first();
            // dd($value);
            if($check){
                $check->qty += $value['qty'];
                $check->save();
            } else {
                $order = Order::create([
                    'product_id' => $value['product_id'],
                    'qty' => $value['qty'],
                    'user_id' => $this->userId,
                    'trx_order_code'=>$trx,
                    'status' => 'pending'
                ]);
            }

        }

        return $this->sendResponse('', 'Put On Chart Success ');
    }
}
