<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\OrdersDetails;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Створення нового замовлення (store)
     * Очікуємо, що фронтенд надішле:
     * - name, phone, address (string)
     * - items: array of { id, title, price, count }
     * - total: число
     */
    public function store(Request $request)
    {
        // Валідуємо
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'required|string|max:500',
            'items' => 'required|array',
            'items.*.id' => 'nullable|integer', // product_id (може бути null)
            'items.*.title' => 'required|string|max:255',
            'items.*.price' => 'required|numeric',
            'items.*.count' => 'required|integer|min:1',
            'total' => 'required|numeric',
        ]);

        // Створюємо запис у таблиці Orders
        $order = Orders::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'total' => $data['total'],
        ]);

        // Зберігаємо товари у таблиці OrdersDetails
        foreach ($data['items'] as $item) {
            OrdersDetails::create([
                'order_id' => $order->id,
                'product_id' => $item['id'] ?? null, // якщо передаєте id
                'title' => $item['title'],
                'price' => $item['price'],
                'count' => $item['count'],
            ]);
        }

        // Повертаємо JSON-відповідь
        return response()->json([
            'message' => 'Order created successfully',
            'order_id' => $order->id,
        ], 201);
    }

    //  Список усіх замовлень
    public function index()
    {
        $orders = Orders::with('items')->get();
        return response()->json($orders);
    }

    //  Конкретне замовлення
    public function show($id)
    {
        $order = Orders::with('items')->findOrFail($id);
        return response()->json($order);
    }
}