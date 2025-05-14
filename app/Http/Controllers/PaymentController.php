<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function success(Request $request)
    {
        $orderId = $request->orderId;
        $order = null;

        if ($orderId) {
            $order = Order::with('appointment')->findOrFail($orderId);

            if ($order->user_id !== auth()->id()) {
                abort(403);
            }
        }

        $order->appointment->update(['status' => AppointmentStatus::Paid]);

        return view('payment.success', compact('order'));
    }
}
