<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function store(Request $request)
    {
        $existingImage = Image::where('product_id', $request->product_id)
            ->where('url', $request->url)
            ->first();

        if ($existingImage) {
            return response()->json($existingImage, 200);
        }
        
        return Image::create($request->all());
    }
}
