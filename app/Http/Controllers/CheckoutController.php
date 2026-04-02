<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Package;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function validateVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'package_id' => 'required|exists:packages,id'
        ]);

        $voucher = Voucher::where('code', $request->code)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$voucher || $voucher->used_count >= $voucher->quota) {
            return response()->json(['message' => 'Voucher tidak valid'], 422);
        }

        $package = Package::find($request->package_id);
        $discountAmount = 0;

        if ($voucher->discount_type === 'percent') {
            $discountAmount = ($package->price * $voucher->discount_value) / 100;
        } else {
            $discountAmount = $voucher->discount_value;
        }

        return response()->json([
            'voucher_id' => $voucher->id,
            'discount_amount' => $discountAmount,
            'total' => $package->price - $discountAmount
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'voucher_id' => 'nullable|exists:vouchers,id'
        ]);

        $package = Package::find($request->package_id);
        $discountAmount = 0;
        $voucher = null;

        if ($request->voucher_id) {
            $voucher = Voucher::find($request->voucher_id);
            if ($voucher->discount_type === 'percent') {
                $discountAmount = ($package->price * $voucher->discount_value) / 100;
            } else {
                $discountAmount = $voucher->discount_value;
            }
        }

        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'user_id' => auth()->id(),
            'package_id' => $package->id,
            'voucher_id' => $voucher?->id,
            'base_price' => $package->price,
            'discount_amount' => $discountAmount,
            'total_amount' => $package->price - $discountAmount,
            'status' => 'pending'
        ]);

        // Integrate with Midtrans here in real scenario
        // For now, we'll just mock it and redirect to a "Success" state
        
        return response()->json([
            'order' => $order,
            'message' => 'Order created successfully'
        ]);
    }
}
