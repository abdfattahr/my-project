<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user();

        if (!$customer) {
            Log::error('فشل المصادقة في /api/customer/invoices', [
                'token' => $request->bearerToken(),
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $invoices = Invoice::where('customer_id', $customer->id)
            ->with(['supermarket' => function ($query) {
                $query->select('id', 'name');
            }])
            ->get()
            ->map(function ($invoice) use ($customer) {
                return [
                    'id' => $invoice->id,
                    'total_price' => $invoice->total_price,
                    'payment_method' => $invoice->payment_method,
                    'status' => $invoice->status,
                    'supermarket_name' => $invoice->supermarket ? $invoice->supermarket->name : null,
                    'customer_name' => $customer->name,
                    'created_at' => $invoice->created_at,
                ];
            });

        return response()->json([
            'message' => 'جميع الفواتير الخاصة بك',
            'customer_name' => $customer->name,
            'invoices' => $invoices,
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $customer = $request->user();

        if (!$customer) {
            Log::error('فشل المصادقة في /api/customer/invoices/show', [
                'token' => $request->bearerToken(),
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $invoice = Invoice::where('id', $id)
            ->where('customer_id', $customer->id)
            ->with(['supermarket' => function ($query) {
                $query->select('id', 'name');
            }])
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'الفاتورة غير موجودة '], 404);
        }

        return response()->json([
            'message' => 'تفاصيل الفاتورة',
            'invoice' => [
                'id' => $invoice->id,
                'total_price' => $invoice->total_price,
                'payment_method' => $invoice->payment_method,
                'status' => $invoice->status,
                'supermarket_name' => $invoice->supermarket ? $invoice->supermarket->name : null,
                'customer_name' => $customer->name,
                'created_at' => $invoice->created_at,
            ],
        ], 200);
    }
}
