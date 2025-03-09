<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Controllers\Customer\BaseController;


class ProductCustomerController extends BaseController
{
    public $user;
    private $model;

    Public $title="Produk";

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function index(Request $request)
    {

        $dataRequest=$request->all();
        $data = $this->model;

        if (isset($request->name)) {
            $data = $data->where('name', 'like', '%' . $request->name . '%');
        }

        if (isset($request->category)) {
            $data = $data->where('category', $request->category);
        }

        $data = $data->orderBy(
            isset($request->range) ? 'price' : 'id',
            isset($request->range) ? ($request->range === 'low' ? 'asc' : 'desc') : 'desc'
        );

        $data=$data->paginate(6)->appends($dataRequest);

        return $this->sendResponse($data, 'Success Load Data');

    }

    public function show($id)
    {
        $data = $this->model->find($id);

        if (!$data) {
            return $this->sendError('Product not found', 'Product not found');
        }
     
        return $this->sendResponse($data, 'Success Load Data');
    }

}
