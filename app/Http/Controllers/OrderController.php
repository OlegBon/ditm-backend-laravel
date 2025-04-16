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
            'address' => 'nullable|string|max:500', // required замінено на nullable
            'delivery_method' => 'nullable|string|in:courier,nposhta',
            'np_city' => 'nullable|string|max:255',
            'np_city_ref' => 'nullable|string|max:255',
            'np_branch' => 'nullable|string|max:255',
            'np_branch_ref' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
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
            'address' => $data['address'] ?? null,
            'delivery_method' => $data['delivery_method'],
            'np_city' => $data['np_city'] ?? null,
            'np_city_ref' => $data['np_city_ref'] ?? null,
            'np_branch' => $data['np_branch'] ?? null,
            'np_branch_ref' => $data['np_branch_ref'] ?? null,
            'status' => $data['status'] ?? 'Pending', // статус за замовчуванням
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

    // Оновлення замовлення
    //  UPDATE /api/order/{id}
    public function update(Request $request, $id)
    {
        $order = Orders::with('items')->findOrFail($id);

        // Валідуємо поля замовлення + items
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:50',
            'address' => 'sometimes|string|max:500',
            'delivery_method' => 'sometimes|string|in:courier,nposhta',
            'np_city' => 'sometimes|string|max:255',
            'np_city_ref' => 'sometimes|string|max:255',
            'np_branch' => 'sometimes|string|max:255',
            'np_branch_ref' => 'sometimes|string|max:255',
            'total' => 'sometimes|numeric',
            'status' => 'sometimes|string|max:50',
            'items' => 'sometimes|array',

            // Для існуючого item: id != null => оновлюємо
            // Для нового item: id = null => створюємо
            'items.*.id' => 'nullable|integer',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.count' => 'required|integer|min:1',
        ]);

        // Оновлюємо поля замовлення
        $order->fill($data);
        $order->save();

        // Якщо є items, обробляємо
        if (isset($data['items'])) {
            foreach ($data['items'] as $itemData) {
                if (!empty($itemData['id'])) {
                    // Оновлюємо існуючий item
                    $item = \App\Models\OrdersDetails::where('order_id', $order->id)
                        ->find($itemData['id']);
                    if ($item) {
                        // Знаходимо product, щоб раптом оновити price/title, якщо потрібно
                        // Але зазвичай при оновленні existing item
                        // може не змінювати product_id.
                        // Залежно від вашої логіки
                        if ($itemData['product_id'] != $item->product_id) {
                            $product = \App\Models\Product::findOrFail($itemData['product_id']);
                            $item->product_id = $product->id;
                            $item->title = $product->title;
                            $item->price = $product->price;
                        }
                        $item->count = $itemData['count'];
                        $item->save();
                    }
                } else {
                    // Новий item (id=null)
                    // Знайдемо product
                    $product = \App\Models\Product::findOrFail($itemData['product_id']);

                    \App\Models\OrdersDetails::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'title' => $product->title,
                        'price' => $product->price,
                        'count' => $itemData['count'],
                    ]);
                }
            }
        }

        // Перераховуємо total
        $order->refresh();
        $sum = 0;
        foreach ($order->items as $itm) {
            $sum += $itm->price * $itm->count;
        }
        $order->total = $sum;
        $order->save();

        return response()->json([
            'message' => 'Order updated successfully',
            'order'   => $order->fresh('items'),
        ]);
    }

    // видаляти конкретний товар за окремим запитом DELETE
    public function removeItem($orderId, $itemId)
    {
        $order = Orders::findOrFail($orderId);
        // Шукаємо OrderItem, що належить цьому замовленню
        $item = \App\Models\OrdersDetails::where('order_id', $order->id)
            ->findOrFail($itemId);

        $item->delete();

        // Перераховуємо total
        $order->load('items'); // перезавантажимо зв'язок
        $sum = 0;
        foreach ($order->items as $itm) {
            $sum += $itm->price * $itm->count;
        }
        $order->total = $sum;
        $order->save();

        return response()->json([
            'message' => 'Item removed successfully',
            'order'   => $order->fresh('items'),
        ]);
    }

    //  Видалити замовлення (для адмінки).
    //  DELETE /api/order/{id}
    public function delete($id)
    {
        $order = Orders::findOrFail($id);

        // Можна спочатку видалити пов'язані OrdersDetails, якщо в БД немає каскадного видалення
        // OrdersDetails::where('order_id', $order->id)->delete();

        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully'
        ]);
    }
}