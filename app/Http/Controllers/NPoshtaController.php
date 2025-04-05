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
        // http://127.0.0.1:8000/api/nposhta/cities?findByСity=Харк
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
        $findByString = $request->input('findByBranch');

        // Перевірка довжини FindByString
        // http://127.0.0.1:8000/api/nposhta/branches?findByBranch=Байр
        if (mb_strlen($findByString) < 3) {
            return response()->json([
                'message' => 'findByBranch має містити щонайменше 3 символи.'
            ], 400); // Повернення помилки 400 (Bad Request)
        }
        
        $apiKey = env('API_NPOSHTA_KEY');
        $url = "https://api.novaposhta.ua/v2.0/json/";
        $data = [
            'modelName' => 'Address',
            'calledMethod' => 'getWarehouses',
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
}
