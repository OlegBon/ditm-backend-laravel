<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\RecentlyViewedProducts;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // public function index()
    // {
    //     return Product::with('images')->get();
    // }

    public function index(Request $request)
    {
        $query = Product::with('images'); // Базовий запит із підключенням зв'язку `images`

        // Перевірка, чи є параметр `category` у запиті
        if ($request->has('category')) {
            $query->where('category', $request->query('category'));
            $query->inRandomOrder(); // Додаємо рандомне впорядкування для категорій
            $products = $query->limit(10)->get(); // Повертаємо максимум 10 товарів
        } else {
            $products = $query->get(); // Повертаємо всі товари без фільтрації
        }

        return response()->json($products);
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

    public function show($id)
    {
        return Product::with('images')->findOrFail($id);
    }

    public function getUserViewedProducts(Request $request)
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }

        $viewed = RecentlyViewedProducts::where('user_id', $userId)
            ->orderByDesc('viewed_at')
            ->with('product.images')
            ->get()
            ->unique('product_id') // Уникнення повторень за полем product_id
            ->take(10) // Беремо лише останні 10
            ->pluck('product');

        return response()->json([
            'success' => true,
            'data' => $viewed
        ]);
    }

    public function addUserViewedProduct(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'product_id' => 'required|integer',
            'viewed_at' => 'nullable|date', // Час перегляду (опціонально)
        ]);

        // Якщо немає `viewed_at`, встановлюємо поточний час
        $data['viewed_at'] = $data['viewed_at'] ?? now();

        // Додаємо запис до таблиці
        RecentlyViewedProducts::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Viewed product added successfully',
        ]);
    }
}
