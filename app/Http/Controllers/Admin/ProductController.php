<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends BaseController
{
    public $user;
    private $model;

    Public $title="Produk";

    public function __construct(Product $model)
    {
        $this->model = $model;

    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $dataRequest=$request->all();
        $data = $this->model;

        if (isset($request->name)) {
            $data = $data->where('name', $request->name);
        }

        if (isset($request->category)) {
            $data = $data->where('category', $request->category);
        }

        $data = $data->orderBy(
            isset($request->range) ? 'price' : 'id',
            isset($request->range) ? ($request->range === 'low' ? 'asc' : 'desc') : 'desc'
        );

        $data=$data->paginate(10)->appends($dataRequest);


        return $this->sendResponse($data, 'Success Load Data');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string|max:255',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        } else {
            $imagePath = null;
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'category' => $request->category,
            'image' => $imagePath
        ]);

        return $this->sendResponse($product, 'Success Add Data');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = $this->model->find($id);

        if (!$data) {
            return $this->sendError('Product not found', 'Product not found');
        }
     
        return $this->sendResponse($data, 'Success Load Data');
    }


    public function update(Request $request, $id)
    {
        $data = Product::find($id);
        if (!$data) {
            return $this->sendError('Product not found', 'Product not found');

        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'category' => 'sometimes|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Validate image
        ]);

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($data->image) {
                Storage::disk('public')->delete($data->image);
            }

            // Store the new image
            $imagePath = $request->file('image')->store('products', 'public');
            $data->image = $imagePath;
        }

        // Update only the provided fields

        $data->update($request->except('image'));

        return $this->sendResponse($data, 'Product updated successfully');
    }

    public function destroy($id)
    {
        $data = Product::find($id);
        if (!$data) {
            return $this->sendError('Product not found', 'Product not found');
        }

        $data->delete();

        return $this->sendResponse([], 'Product deleted successfully');
    }
}
