<?php

namespace App\Http\Controllers;

use App\Models\Customer_Favorites;
use Illuminate\Http\Request;

class Customer_FavoritesController extends Controller
{
    public function index(Request $request)
    {
        $favorites = Customer_Favorites::where('customer_id', $request->user()->id)
            ->where('is_active', true)
            ->with('product')
            ->get();
        return response()->json([
            'message' => $favorites->isEmpty() ? 'لا توجد مفضلات حاليًا' : 'المفضلات الخاصة بك',
            'favorites' => $favorites
        ], 200);
        return response()->json([
            'message' => 'المفضلات الخاصة بك',
            'favorites' => $favorites],200);
    }
    // إضافة منتج إلى المفضلة
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'notes' => 'nullable|string|max:255',
        ]);

        // التحقق مما إذا كان المنتج موجودًا بالفعل في المفضلة
        $exists = Customer_Favorites::where('customer_id', $request->customer_id)
            ->where('product_id', $request->product_id)
            ->where('is_active', true)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'المنتج موجود بالفعل في المفضلة'
            ], 400);
        }

        $favorite = Customer_Favorites::create([
            'customer_id' => $request->customer_id,
            'product_id' => $request->product_id,
            'notes' => $request->notes,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'تم إضافة المنتج إلى المفضلة بنجاح',
            'favorite' => $favorite->load('product')
        ], 201);
    }




    //!            destroy



    public function destroy(Request $request, $favoriteId)
    {
        $favorite = Customer_Favorites::where('id', $favoriteId)
            ->where('customer_id', $request->user()->id)
            ->where('is_active', true)

            ->first();

        if (!$favorite) {
            return response()->json([
                'message'=> 'المفضلة غير موجودة أو تم حذفها مسبقًا'
            ], 404);
        }
        //$favorite->update(['is_active' => false]);
        $favorite->delete();
        return response()->json([
            'message'=> 'تم حذف المنتج من المفضلة بنجاح'
        ], 200);

    }
}
