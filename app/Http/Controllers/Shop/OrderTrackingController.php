<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function index(): View
    {
        return view('shop.track', ['order' => null, 'searched' => false]);
    }

    public function lookup(Request $request): View
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'min:1'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $order = Order::with(
            'cakeCustomizations.cakeSize',
            'cakeCustomizations.layers.spongeFlavor',
            'cakeCustomizations.layers.creamFlavor'
        )->find($request->integer('order_id'));

        $digits = fn (string $phone) => preg_replace('/\D+/', '', $phone);

        if ($order && $digits($order->customer_phone) !== $digits($validated['phone'])) {
            $order = null;
        }

        return view('shop.track', ['order' => $order, 'searched' => true]);
    }

    public function dismiss(int $orderId): RedirectResponse
    {
        if (in_array($orderId, session('my_orders', []))) {
            session()->push('dismissed_orders', $orderId);
        }

        return back();
    }
}
