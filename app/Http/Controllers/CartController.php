<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceOrderRequest;
use App\Http\Requests\StoreCakeCustomizationRequest;
use App\Models\CakeDesign;
use App\Models\CakeSize;
use App\Models\CreamFlavor;
use App\Models\Order;
use App\Models\SpongeFlavor;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CartController extends Controller
{
    public function store(StoreCakeCustomizationRequest $request, CartService $cart): JsonResponse
    {
        $validated = $request->validated();
        $submitted = array_values($validated['layers']);

        $size = CakeSize::findOrFail($request->integer('cake_size_id'));

        // Load every flavor once, then look each layer up. Names and prices come from the database.
        $sponges = SpongeFlavor::whereIn('id', array_column($submitted, 'sponge_flavor_id'))->get()->keyBy('id');
        $creams = CreamFlavor::whereIn('id', array_column($submitted, 'cream_flavor_id'))->get()->keyBy('id');

        $unitPrice = (float) $size->price;
        $layers = [];

        foreach ($submitted as $index => $layer) {
            $sponge = $sponges[(int) $layer['sponge_flavor_id']];
            $cream = $creams[(int) $layer['cream_flavor_id']];

            $unitPrice += (float) $sponge->extra_price + (float) $cream->extra_price;

            $layers[] = [
                'position' => $index + 1,
                'sponge_id' => $sponge->id,
                'sponge_name' => $sponge->name,
                'cream_id' => $cream->id,
                'cream_name' => $cream->name,
            ];
        }

        // The design's price comes from the database too.
        $design = $request->filled('cake_design_id') ? CakeDesign::with('frames')->findOrFail($request->integer('cake_design_id')) : null;
        $unitPrice += $design ? (float) $design->extra_price : 0;
        $cover = $design?->frames->first()?->image;

        $cart->add([
            'size_id' => $size->id,
            'size_label' => $size->label,
            'design_id' => $design?->id,
            'design_name' => $design?->name,
            'design_image' => $cover,
            'layers' => $layers,
            'message' => $validated['message'] ?? null,
            'quantity' => (int) $validated['quantity'],
            'price' => round($unitPrice, 2),
        ]);

        return response()->json(['success' => true, 'count' => $cart->count()]);
    }

    public function index(CartService $cart): View
    {
        return view('cart.index', [
            'items' => $cart->items(),
            'total' => $cart->total(),
        ]);
    }

    public function destroy(string $itemId, CartService $cart): RedirectResponse
    {
        $cart->remove($itemId);

        return redirect()->route('cart.index')->with('success', 'Cake removed from your cart.');
    }

    public function placeOrder(PlaceOrderRequest $request, CartService $cart): RedirectResponse
    {
        $items = $cart->items();

        if (empty($items)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $validated = $request->validated();
        $total = $cart->total();

        $order = DB::transaction(function () use ($validated, $items, $total) {
            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_address' => $validated['customer_address'],
                'delivery_date' => $validated['delivery_date'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'total' => $total,
            ]);

            foreach ($items as $item) {
                $first = $item['layers'][0];

                $customization = $order->cakeCustomizations()->create([
                    'cake_size_id' => $item['size_id'],
                    // The original single-flavor columns mirror layer 1. The layers table is the source of truth.
                    'sponge_flavor_id' => $first['sponge_id'],
                    'cream_flavor_id' => $first['cream_id'],
                    'message' => $item['message'],
                    'quantity' => $item['quantity'] ?? 1,
                    'price' => $item['price'],
                    // Cart items added before designs existed have none.
                    'cake_design_id' => $item['design_id'] ?? null,
                    'design_name' => $item['design_name'] ?? null,
                ]);

                $customization->layers()->createMany(array_map(fn (array $layer) => [
                    'position' => $layer['position'],
                    'sponge_flavor_id' => $layer['sponge_id'],
                    'cream_flavor_id' => $layer['cream_id'],
                ], $item['layers']));
            }

            return $order;
        });

        $cart->clear();

        session()->push('my_orders', $order->id);

        return redirect()
            ->route('cake-studio.index')
            ->with('success', "Order placed! Order #{$order->id}, Total: ৳{$order->total}. Keep your order number: you can check its status any time under Track order.");
    }
}
