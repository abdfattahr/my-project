<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Supermarket;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * عرض قائمة المتاجر مع المنتجات المرتبطة
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Supermarket::with([
            'products' => function ($query) use ($request) {
                $query->select('products.id', 'products.name', 'products.subcategory_id', 'products.trade_mark_id', 'supermarkt_products.stock');
                // تصفية المنتجات حسب اسم المنتج
                if ($request->has('product_name')) {
                    $query->where('products.name', 'like', '%' . $request->input('product_name') . '%');
                }
                // تصفية حسب القسم الفرعي
                if ($request->has('subcategory_id')) {
                    $query->where('products.subcategory_id', $request->input('subcategory_id'));
                }
                // تصفية حسب العلامة التجارية
                if ($request->has('trade_mark_id')) {
                    $query->where('products.trade_mark_id', $request->input('trade_mark_id'));
                }
            },
            'products.subcategory' => function ($query) use ($request) { // إضافة use ($request)
                $query->select('id', 'name', 'main_category_id', 'icon');
                // تصفية حسب اسم القسم الفرعي
                if ($request->has('subcategory_name')) {
                    $query->where('subcategories.name', 'like', '%' . $request->input('subcategory_name') . '%');
                }
            },
            'products.tradeMark' => function ($query) use ($request) { // إضافة use ($request)
                $query->select('id', 'name');
                // تصفية حسب اسم العلامة التجارية
                if ($request->has('trade_mark_name')) {
                    $query->where('trade_marks.name', 'like', '%' . $request->input('trade_mark_name') . '%');
                }
            }
        ]);

        // تصفية المتاجر حسب اسم المتجر
        if ($request->has('supermarket_name')) {
            $query->where('name', 'like', '%' . $request->input('supermarket_name') . '%');
        }

        $supermarkets = $query->get();

        // إذا ما في متاجر تطابق المعايير
        if ($supermarkets->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا توجد متاجر أو منتجات تطابق معايير البحث',
            ], 404);
        }

        // تنظيف البيانات لإزالة المتاجر بدون منتجات
        $supermarkets = $supermarkets->filter(function ($supermarket) {
            return $supermarket->products->isNotEmpty();
        });

        return response()->json([
            'status' => 'success',
            'data' => $supermarkets->values(), // إعادة ترقيم الفهرس بعد التصفية
        ], 200);
    }

/**
     * عرض تفاصيل منتج مع مراعاة السوبرماركت
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        $query = Product::with([
            'subcategory' => function ($query) {
                $query->select('id', 'name', 'main_category_id', 'icon');
            },
            'tradeMark' => function ($query) {
                $query->select('id', 'name');
            },
            'supermarkets' => function ($query) use ($request) {
                $query->select('supermarkets.id', 'supermarkets.name', 'supermarkt_products.stock');
                if ($request->has('supermarket_id')) {
                    $query->where('supermarkets.id', $request->input('supermarket_id'));
                }
            },
            'favoriteByCustomers' => function ($query) {
                $query->select('customers.id', 'customers.name', 'customer_favorites.is_active', 'customer_favorites.notes');
            }
        ]);

        $product = $query->find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'المنتج غير موجود',
            ], 404);
        }

        if ($request->has('supermarket_id') && !$product->supermarkets->contains('id', $request->input('supermarket_id'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'المنتج غير متاح في السوبرماركت المحدد',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $product,
        ], 200);
    }
}
