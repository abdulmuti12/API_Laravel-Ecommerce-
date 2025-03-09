<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Customer\BaseController;
use App\Models\Customer;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;



class EmailController extends BaseController
{
    private $model;
    private $userId;

    Public $title="Email";

    public function __construct(Customer $model)
    {
        $this->model = $model;
        $this->userId=JWTAuth::parseToken()->authenticate()->id;
    }

    public function index(Request $request){

        $dataRequest=$request->all();
        $data = $this->model;

        // if (isset($dataRequest)) {
        //     $data = $data->where('name', $request->name)->orWhere('email', $request->email);
        // }

        // if (isset($request->email)) {
        //     $data = $data->where('email', $request->email);
        // }

        $data = $data->orderBy('id' , 'desc');

        $data=$data->paginate(10)->appends($dataRequest);


        return $this->sendResponse($data, 'Success Load Data');
    }

    public function show($id)
    {
        $data = $this->model->find($id);

        if (!$data) {
            return $this->sendError('Data not found', 'Data not found');
        }
     
        return $this->sendResponse($data, 'Success Load Data');
    }

    public function sendMail(Request $request){

      ;

        $dataCustomer=Customer::where('subscription',1)->get();

        foreach ($dataCustomer as $key => $value) {
            
            $data['name']=$value->name;
            $data['message']="email";
            
            Mail::to($value->email)->send(new SendMail($data));
        }

        return $this->sendResponse($data, 'Send Email Success');

    }   

}
