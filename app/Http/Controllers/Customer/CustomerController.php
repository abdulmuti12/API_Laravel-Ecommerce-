<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Customer\BaseController;

class CustomerController extends BaseController
{
    public $user;
    private $model;

    Public $title="Customer";

    public function __construct(Customer $model)
    {
        $this->model = $model;
    }

    public function index(){

    }
    public function personalData($id){

        $data = $this->model->find($id);

        if (!$data) {
            return $this->sendError('Product not found', 'Product not found');
        }
     
        return $this->sendResponse($data, 'Success Load Data');
    }

    public function show($id)
    {
        $data = $this->model->find($id);

        if (!$data) {
            return $this->sendError('Personal Data not found', 'Personal Data not found');
        }
     
        return $this->sendResponse($data, 'Success Load Data');
    }

    public function updateSubscribe($id)
    {
        $data = $this->model->find($id);

        // dd($id);

        if (!$data) {
            return $this->sendError('Personal Data not found', 'Personal Data not found');
        }

        if($data->subscription == 1){
            $data->subscription = 0;
        }else{
            $data->subscription = 1;
        }

        $data->save();
     
        return $this->sendResponse($data, 'Success Update Subscribe');
    }

}
