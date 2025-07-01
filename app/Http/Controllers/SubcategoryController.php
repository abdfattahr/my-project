<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Subcategorie;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
{
    /**
     * عرض قائمة الأقسام الفرعية المرتبطة بالقسم الرئيسي مع المنتجات
     *
     */
    public function index()
    {
        $query = Subcategorie::with(['products' => function ($query) {
            $query->select('id', 'name', 'price', 'subcategory_id', 'image');
        }]);

        $subcategories = $query->get();

        if ($subcategories->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا توجد أقسام فرعية متاحة لهذا القسم الرئيسي',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $subcategories,
        ], 200);
    }

    /**
     * عرض تفاصيل قسم فرعي معين مع المنتجات المرتبطة به مع التحقق من القسم الرئيسي
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, Request $request)
    {
        $query = Subcategorie::with(['products' => function ($query) {
            $query->select('id', 'name', 'price', 'subcategory_id', 'image');
        }]);

        // إذا تم تمرير main_category_id، تحقق من الارتباط
        if ($request->has('main_category_id')) {
            $query->where('main_category_id', $request->input('main_category_id'));
        }

        $subcategory = $query->find($id);

        if (!$subcategory) {
            return response()->json([
                'status' => 'error',
                'message' => 'القسم الفرعي غير موجود أو غير مرتبط بالقسم الرئيسي المحدد',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $subcategory,
        ], 200);
    }
}
