<?php

namespace App\Http\Controllers;

use App\Models\Advertisement_Supermarket;
use Illuminate\Http\Request;

class Advertisement_SupermarketController extends Controller
{
    public function index()
    {
        $advertisements = Advertisement_Supermarket::with(['supermarket', 'advertisement'])
            ->orderBy('date_publication', 'desc') // ترتيب حسب تاريخ النشر
            ->get();

        return response()->json([
            'message' => 'إعلانات المتاجر',
            'advertisements' => $advertisements
        ], 200);
    }

   public function showBySupermarket($supermarketId)
{
    // جلب الإعلانات الخاصة بمتجر معين بناءً على supermarket_id
    $advertisements = Advertisement_Supermarket::where('supermarket_id', $supermarketId)
        ->with(['supermarket', 'advertisement'])
        ->orderBy('date_publication', 'desc')
        ->get();

    if ($advertisements->isEmpty()) {
        return response()->json([
            'message' => 'لا توجد إعلانات لهذا المتجر'
        ], 404);
    }

    return response()->json([
        'message' => 'إعلانات المتجر',
        'advertisements' => $advertisements
    ], 200);
}
public function showByAdvertisement($id)
{
    $advertisementDetails = Advertisement_Supermarket::where('id', $id)
        ->with(['supermarket', 'advertisement'])
        ->orderBy('date_publication', 'desc')
        ->first();

    if (!$advertisementDetails) {
        return response()->json([
            'message' => 'الإعلان غير موجود أو لا يوجد تفاصيل مرتبطة'
        ], 404);
    }

    return response()->json([
        'message' => 'تفاصيل الإعلان',
        'advertisement' => $advertisementDetails
    ], 200);
}
}
