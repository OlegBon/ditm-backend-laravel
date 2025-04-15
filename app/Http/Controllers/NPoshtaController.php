<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NPoshtaController extends Controller
{
    public function getCities(Request $request)
    {
        $findByString = $request->input('findByCity');

        // Перевірка довжини FindByString
        // http://127.0.0.1:8000/api/nposhta/cities?findByCity=Харк
        if (mb_strlen($findByString) < 3) {
            return response()->json([
                'message' => 'findByCity має містити щонайменше 3 символи.'
            ], 400); // Повернення помилки 400 (Bad Request)
        }

        $apiKey = env('API_NPOSHTA_KEY');
        $url = "https://api.novaposhta.ua/v2.0/json/";
        $data = [
            'modelName' => 'Address',
            'calledMethod' => 'getCities',
            'apiKey' => $apiKey,
            'methodProperties' => [
                'FindByString' => $findByString,
                // 'Limit' => $request->input('limit', 10),
            ]
        ];

        // $response = \Http::post($url, $data);
        $response = \Http::withOptions([
            'verify' => false,
        ])->post($url, $data);

        return response()->json($response->json());
    }
    
    public function getBranches(Request $request)
    {
        $cityRef = $request->input('cityRef'); // Унікальний референс міста
        $findByString = $request->input('findByBranch'); // Пошук за назвою або номером відділення

        // Визначення, чи введення є цифрами
        if (ctype_digit($findByString)) {
            // Якщо це цифри, виконувати пошук за номером відділення
            // http://127.0.0.1:8000/api/nposhta/branches?findByBranch=65
            // http://127.0.0.1:8000/api/nposhta/branches?cityRef=db5c88e0-391c-11dd-90d9-001a92567626&findByBranch=65 - cityRef для Харківа
            $apiKey = env('API_NPOSHTA_KEY');
            $url = "https://api.novaposhta.ua/v2.0/json/";
            $data = [
                'modelName' => 'Address',
                'calledMethod' => 'getWarehouses',
                'apiKey' => $apiKey,
                'methodProperties' => [
                    'CityRef' => $cityRef, // Фільтр по місту
                    'FindByString' => $findByString, // Пошук за ключовим словом
                ],
            ];

            $response = \Http::withOptions([
                'verify' => false,
            ])->post($url, $data);

            $warehouses = $response->json()['data'] ?? [];

            // Фільтрація відділень по номеру
            $filteredWarehouses = array_filter($warehouses, function ($warehouse) use ($findByString) {
                return $warehouse['Number'] === $findByString;
            });

            return response()->json([
                'success' => true,
                'data' => array_values($filteredWarehouses) // Очищення ключів
            ]);

        } else {
            // Перевірка довжини для пошуку за назвою
            if (mb_strlen($findByString) < 3) {
                return response()->json([
                    'message' => 'findByBranch має містити щонайменше 3 символи.'
                ], 400); // Повернення помилки 400 (Bad Request)
            }

            // Пошук за назвою відділення
            // http://127.0.0.1:8000/api/nposhta/branches?findByBranch=Бай
            // http://127.0.0.1:8000/api/nposhta/branches?cityRef=db5c88e0-391c-11dd-90d9-001a92567626&findByBranch=Бай - cityRef для Харківа
            $apiKey = env('API_NPOSHTA_KEY');
            $url = "https://api.novaposhta.ua/v2.0/json/";
            $data = [
                'modelName' => 'Address',
                'calledMethod' => 'getWarehouses',
                'apiKey' => $apiKey,
                'methodProperties' => [
                    'CityRef' => $cityRef, // Фільтр по місту
                    'FindByString' => $findByString, // Пошук за ключовим словом
                ],
            ];

            $response = \Http::withOptions([
                'verify' => false,
            ])->post($url, $data);

            // Перевіряємо відповідь від API
            $warehouses = $response->json()['data'] ?? [];

            // Якщо дані присутні, повертаємо успіх
            return response()->json([
                'success' => true,
                'data' => $warehouses
            ]);
        }
    }
}
