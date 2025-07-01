<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Supermarket;
use App\Models\MainCategorie;

class SupermarketController extends Controller
{
    /**
     * عرض قائمة كل المتاجر للعملاء (بدون حماية)
     */
    public function index()
    {
        $supermarkets = Supermarket::with(['user' => function ($query) {
            $query->select('id', 'name', 'email');
        }])
        ->get()
        ->map(function ($supermarket) {
            return [
                'id' => $supermarket->id,
                'name' => $supermarket->name,
                'position' => $supermarket->position,
                'image' => $supermarket->image,
                'phone_number' => $supermarket->phone_number,
                'created_at' => $supermarket->created_at,
                'vendor' => [
                    'id' => $supermarket->user->id,
                    'name' => $supermarket->user->name,
                    'email' => $supermarket->user->email,
                ],
            ];
        });

        if ($supermarkets->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا توجد متاجر متاحة'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم استرجاع قائمة المتاجر بنجاح',
            'data' => $supermarkets
        ], 200);
    }

    /**
     * عرض تفاصيل متجر معين للعملاء (بدون حماية)
     */
    public function show($id)
    {
        $supermarket = Supermarket::with(['user' => function ($query) {
            $query->select('id', 'name', 'email');
        }])
        ->find($id);

        if (!$supermarket) {
            return response()->json([
                'status' => 'error',
                'message' => 'المتجر غير موجود'
            ], 404);
        }

        $mainCategories = MainCategorie::select('id', 'name', 'icon')->get();
        $supermarketData = [
            'id' => $supermarket->id,
            'name' => $supermarket->name,
            'position' => $supermarket->position,
            'image' => $supermarket->image,
            'phone_number' => $supermarket->phone_number,
            'created_at' => $supermarket->created_at,
            'vendor' => [
                'id' => $supermarket->user->id,
                'name' => $supermarket->user->name,
                'email' => $supermarket->user->email,
            ],
            'products' => $supermarket->products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'image' => $product->image,
                    'name' => $product->name,
                    'price' => $product->price,
                    'description' => $product->description,
                    'created_at' => $product->created_at,
                ];
            })->toArray(),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'تم استرجاع تفاصيل المتجر بنجاح',
            'data' => [$supermarketData],
            'main_categories' => $mainCategories
        ], 200);
    }
}
