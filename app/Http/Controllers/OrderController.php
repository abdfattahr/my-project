<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\SupermarktProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user();

        if (!$customer) {
            Log::error('فشل المصادقة في /api/customer/orders', [
                'token' => $request->bearerToken(),
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'غير مصادق عليه'], 401);
        }

        $orders = Order::whereHas('invoice', function ($query) use ($customer) {
                $query->where('customer_id', $customer->id);
            })
            ->with([
                'product' => function ($query) {
                    $query->select('id', 'name', 'price', 'image');
                },
                'invoice.supermarket' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->get()
            ->map(function ($order) use ($customer) {
                return [
                    'id' => $order->id,
                    'product' => $order->product ? [
                        'id' => $order->product->id,
                        'name' => $order->product->name,
                        'price' => $order->product->price,
                        'image' => $order->product->image,
                    ] : null,
                    'unit_price' => $order->unit_price,
                    'amount' => $order->amount,
                    'date_order' => $order->date_order,
                    'location' => $order->location,
                    'status' => $order->status,
                    'customer_name' => $customer->name,
                    'supermarket_name' => $order->invoice->supermarket ? $order->invoice->supermarket->name : null,
                    'created_at' => $order->created_at,
                ];
            });

        return response()->json([
            'message' => 'جميع الطلبات الخاصة بك',
            'customer_name' => $customer->name,
            'orders' => $orders
        ], 200);
    }

    public function store(Request $request)
    {
        $customer = $request->user();

        if (!$customer) {
            Log::error('فشل المصادقة في /api/customer/orders/store', [
                'token' => $request->bearerToken(),
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'غير مصادق عليه'], 401);
        }

        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.supermarket_id' => 'required|exists:supermarkets,id',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.amount' => 'required|integer|min:1',
            'date_order' => 'required|date',
            'location' => 'required|string',
            'payment_method' => 'required|in:cash,points', // إضافة التحقق من طريقة الدفع
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'فشل التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        $orders = [];
        $invoices = [];

        $productsBySupermarket = collect($request->products)->groupBy('supermarket_id');

        foreach ($productsBySupermarket as $supermarketId => $products) {
            $totalPrice = 0;

            foreach ($products as $product) {
                $supermarktProduct = SupermarktProduct::where('supermarket_id', $product['supermarket_id'])
                    ->where('product_id', $product['product_id'])
                    ->first();

                if (!$supermarktProduct) {
                    return response()->json([
                        'message' => "المنتج {$product['product_id']} غير متوفر في المتجر {$product['supermarket_id']}"
                    ], 404);
                }

                if ($supermarktProduct->stock < $product['amount']) {
                    return response()->json([
                        'message' => "الكمية غير متوفرة في المخزون للمنتج {$product['product_id']}",
                        'available_stock' => $supermarktProduct->stock
                    ], 400);
                }

                $totalPrice += $product['unit_price'] * $product['amount'];
            }

            $invoice = $this->createInvoiceForOrder($customer->id, $supermarketId, $totalPrice, $request->payment_method);
            $invoices[] = $invoice;

            foreach ($products as $product) {
                $order = Order::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product['product_id'],
                    'unit_price' => $product['unit_price'],
                    'amount' => $product['amount'],
                    'date_order' => $request->date_order,
                    'location' => $request->location,
                    'status' => 'pending',
                ]);

                $supermarktProduct = SupermarktProduct::where('supermarket_id', $product['supermarket_id'])
                    ->where('product_id', $product['product_id'])
                    ->first();
                $supermarktProduct->decrement('stock', $product['amount']);

                $orders[] = [
                    'id' => $order->id,
                    'product' => $order->product ? [
                        'id' => $order->product->id,
                        'name' => $order->product->name,
                        'price' => $order->product->price,
                        'image' => $order->product->image,
                    ] : null,
                    'unit_price' => $order->unit_price,
                    'amount' => $order->amount,
                    'date_order' => $order->date_order,
                    'location' => $order->location,
                    'status' => $order->status,
                    'customer_name' => $customer->name,
                    'supermarket_name' => $invoice->supermarket ? $invoice->supermarket->name : null,
                    'created_at' => $order->created_at,
                ];
            }
        }

        return response()->json([
            'message' => 'تم إنشاء الطلبات بنجاح',
            'orders' => $orders,
            'invoices' => $invoices
        ], 201);
    }

    public function update(Request $request, $orderId)
    {
        $customer = $request->user();

        if (!$customer) {
            Log::error('فشل المصادقة في /api/customer/orders/update', [
                'token' => $request->bearerToken(),
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'غير مصادق عليه'], 401);
        }

        $order = Order::where('id', $orderId)
            ->whereHas('invoice', function ($query) use ($customer) {
                $query->where('customer_id', $customer->id);
            })
            ->with(['invoice.supermarket' => function ($query) {
                $query->select('id', 'name');
            }])
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'الطلب غير موجود أو لا يخصك'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,accepted,rejected',
            'payment_method' => 'required_if:status,accepted|in:cash,points',
            'location' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'فشل التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->status = $request->status;
        if ($request->filled('location')) {
            $order->location = $request->location;
        }
        $order->save();

        if ($request->status === 'accepted') {
            $this->handleInvoiceOnOrderConfirmation($order, $request->payment_method, $customer);
        }

        return response()->json([
            'message' => 'تم تحديث الطلب بنجاح',
            'order' => [
                'id' => $order->id,
                'product' => $order->product ? [
                    'id' => $order->product->id,
                    'name' => $order->product->name,
                    'price' => $order->product->price,
                    'image' => $order->product->image,
                ] : null,
                'unit_price' => $order->unit_price,
                'amount' => $order->amount,
                'date_order' => $order->date_order,
                'location' => $order->location,
                'status' => $order->status,
                'customer_name' => $customer->name,
                'supermarket_name' => $order->invoice->supermarket ? $order->invoice->supermarket->name : null,
                'created_at' => $order->created_at,
            ],
        ], 200);
    }

    public function destroy(Request $request, $orderId)
    {
        $customer = $request->user();

        if (!$customer) {
            Log::error('فشل المصادقة في /api/customer/orders/destroy', [
                'token' => $request->bearerToken(),
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'غير مصادق عليه'], 401);
        }

        $order = Order::where('id', $orderId)
            ->whereHas('invoice', function ($query) use ($customer) {
                $query->where('customer_id', $customer->id);
            })
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'الطلب غير موجود أو لا يخصك'
            ], 404);
        }

        if ($order->status !== 'rejected') {
            $supermarktProduct = SupermarktProduct::where('product_id', $order->product_id)
                ->first();

            if ($supermarktProduct) {
                $supermarktProduct->increment('stock', $order->amount);
            }
        }

        $order->delete();

        return response()->json([
            'message' => 'تم حذف الطلب بنجاح'
        ], 200);
    }

    private function createInvoiceForOrder($customerId, $supermarketId, $totalPrice, $paymentMethod)
    {
        return Invoice::create([
            'customer_id' => $customerId,
            'supermarket_id' => $supermarketId,
            'total_price' => $totalPrice,
            'information' => 'فاتورة تم إنشاؤها تلقائيًا عند إنشاء طلب',
            'payment_method' => $paymentMethod,
            'status' => 'pending',
        ]);
    }

    private function handleInvoiceOnOrderConfirmation(Order $order, $paymentMethod, $customer)
    {
        $invoice = $order->invoice;

        if ($invoice) {
            $invoice->update([
                'payment_method' => $paymentMethod,
                'status' => 'accepted',
            ]);

            $pointsToAdd = $order->amount * 6; // 6 نقاط لكل وحدة
            $customer->increment('points', $pointsToAdd);

            Log::info('تم إضافة نقاط للزبون', [
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'points_added' => $pointsToAdd,
            ]);
        }
    }
}
