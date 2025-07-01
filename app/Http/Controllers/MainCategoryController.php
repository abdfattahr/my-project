<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MainCategorie;
use Illuminate\Http\Request;

class MainCategoryController extends Controller
{
    /**
     * عرض قائمة الأقسام الرئيسية فقط
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = MainCategorie::query();

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $mainCategories = $query->get();

        if ($mainCategories->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'لا توجد أقسام رئيسية متاحة',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $mainCategories,
        ], 200);
    }

    /**
     * عرض تفاصيل قسم رئيسي معين مع الأقسام الفرعية المرتبطة
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $mainCategory = MainCategorie::with(['subcategories' => function ($query) {
            $query->select('id', 'name', 'main_category_id', 'icon');
        }])->find($id);

        if (!$mainCategory) {
            return response()->json([
                'status' => 'error',
                'message' => 'القسم الرئيسي غير موجود',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $mainCategory,
        ], 200);
    }
}
