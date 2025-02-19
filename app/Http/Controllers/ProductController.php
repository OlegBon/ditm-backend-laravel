<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    public function store(Request $request)
    {
        $existingProduct = Product::where('title', $request->title)
            ->where('description', $request->description)
            ->first();

        if ($existingProduct) {
            return response()->json($existingProduct, 200);
        }
        
        // return response(['allDate' => $request->all()], 200);
        return Product::create($request->all());
    }
}
