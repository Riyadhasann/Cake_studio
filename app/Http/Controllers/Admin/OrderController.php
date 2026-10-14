<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CakeCustomizationLayer;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::with(
            'cakeCustomizations.cakeSize',
            'cakeCustomizations.layers.spongeFlavor',
            'cakeCustomizations.layers.creamFlavor'
        )
            ->oldest()
            ->get();

        return view('admin.orders.index', ['orders' => $orders]);
    }

    public function deliver(Order $order): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', "Order #{$order->id} is already {$order->status}.");
        }

        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        return back()->with('success', "Order #{$order->id} marked as delivered.");
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', "Order #{$order->id} is already {$order->status}.");
        }

        $validated = $request->validate(
            ['cancel_reason' => ['required', 'string', 'min:3', 'max:500']],
            ['cancel_reason.required' => 'Please write a reason for cancelling the order.']
        );

        $order->update([
            'status' => 'cancelled',
            'cancel_reason' => $validated['cancel_reason'],
            'cancelled_at' => now(),
        ]);

        return back()->with('success', "Order #{$order->id} cancelled. The customer will see your reason.");
    }

    public function destroy(Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order) {
            $customizationIds = $order->cakeCustomizations()->pluck('id');

            CakeCustomizationLayer::whereIn('cake_customization_id', $customizationIds)->delete();
            $order->cakeCustomizations()->delete();
            $order->delete();
        });

        return back()->with('success', "Order #{$order->id} was deleted.");
    }
}
