<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
class CustomerController extends Controller
{
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|numeric',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'فشل التحقق من البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Customer::where('phone_number', $request->phone_number)->first();

        if ($customer) {
            // التحقق من كلمة المرور
            if (is_null($customer->password)) {
                // إذا كان حقل password فارغًا، قم بتحديثه بكلمة المرور الجديدة
                $customer->password = $request->password; // سيتم تشفيرها تلقائيًا
                $customer->save();
            } elseif (!Hash::check($request->password, $customer->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'كلمة المرور غير صحيحة'
                ], 401);
            }
        } else {
            $customer = Customer::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'password' => $request->password,
                'postion' => $request->postion,
                'points' => 0,
            ]);
        }

        $token = $customer->createToken('customer-token')->plainTextToken;

        $customerData = [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone_number' => $customer->phone_number,
            'postion' => $customer->postion,
            'points' => $customer->points,
            'created_at' => $customer->created_at,
            'invoices' => $customer->invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'total_price' => $invoice->total_price ?? 0,
                    'created_at' => $invoice->created_at,
                ];
            })->toArray(),
        ];

        return response()->json([
            'status' => 'success',
            'message' => $customer->wasRecentlyCreated ? 'تم إنشاء الحساب بنجاح' : 'تم تسجيل الدخول بنجاح',
            'customer' => $customerData,
            'token' => $token
        ], 200);
    }
    public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'phone_number' => 'required|numeric',
        'password' => 'required|string|min:6',
        'password_confirmation' => 'required_with:password|same:password',
        'postion' => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'فشل التحقق من البيانات',
            'errors' => $validator->errors()
        ], 422);
    }

    if (Customer::where('phone_number', $request->phone_number)->exists()) {
        return response()->json([
            'status' => 'error',
            'message' => 'لديك حساب بالفعل، يرجى   تسجيل الدخول أو تسجيل حساب جديد'
        ], 409);
    }

    $customer = Customer::create([
        'name' => $request->name,
        'phone_number' => $request->phone_number,
        'password' => $request->password,
        'postion' => $request->postion,
        'points' => 0,
    ]);

    $token = $customer->createToken('customer-token')->plainTextToken;

    $customerData = [
        'id' => $customer->id,
        'name' => $customer->name,
        'phone_number' => $customer->phone_number,
        'postion' => $customer->postion,
        'points' => $customer->points,
        'created_at' => $customer->created_at,
        'invoices' => [],
    ];

    return response()->json([
        'status' => 'success',
        'message' => 'تم إنشاء الحساب وتسجيل الدخول بنجاح',
        'customer' => $customerData,
        'token' => $token
    ], 201);
}
public function profile(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'غير مصادق عليه، يرجى تسجيل الدخول أولاً'
        ], 401);
    }

    $customerData = [
        'id' => $user->id,
        'name' => $user->name,
        'phone_number' => $user->phone_number,
        'postion' => $user->postion,
        'points' => $user->points,
        'created_at' => $user->created_at,
        'invoices' => $user->invoices->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'total_price' => $invoice->total_price ?? 0,
                'created_at' => $invoice->created_at,
            ];
        })->toArray(),
    ];

    return response()->json([
        'status' => 'success',
        'message' => 'تم استرجاع بيانات الملف الشخصي بنجاح',
        'customer' => $customerData
    ], 200);
}


public function logout(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'غير مصادق عليه، يرجى تسجيل الدخول أولاً'
        ], 401);
    }

    $user->tokens()->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'تم تسجيل الخروج بنجاح'
    ], 200);
}
}
