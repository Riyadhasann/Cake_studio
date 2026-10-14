<?php

namespace App\View\Components;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OrderAlerts extends Component
{
    public function render(): View
    {
        $ids = array_diff(session('my_orders', []), session('dismissed_orders', []));

        $orders = $ids === []
            ? collect()
            : Order::whereIn('id', $ids)->where('status', 'cancelled')->get();

        return view('components.order-alerts', ['orders' => $orders]);
    }
}