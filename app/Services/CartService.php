<?php

namespace App\Services;

use Illuminate\Support\Str;

class CartService
{
    protected string $sessionKey = 'cake_cart';

    /** @param  array<string, mixed>  $item */
    public function add(array $item): void
    {
        $items = $this->items();
        $item['id'] = (string) Str::uuid();
        $items[] = $item;

        session([$this->sessionKey => $items]);
    }

    public function remove(string $id): void
    {
        $items = array_values(array_filter(
            $this->items(),
            fn (array $item) => ($item['id'] ?? null) !== $id
        ));

        session([$this->sessionKey => $items]);
    }

    /** @return list<array<string, mixed>> */
    public function items(): array
    {
        return array_values(array_map(
            fn (array $item) => $this->withLayers($item),
            session($this->sessionKey, [])
        ));
    }

    public function total(): float
    {
        return round(collect($this->items())->sum(
            fn (array $item) => $item['price'] * ($item['quantity'] ?? 1)
        ), 2);
    }

    public function count(): int
    {
        return (int) collect($this->items())->sum(fn (array $item) => $item['quantity'] ?? 1);
    }

    public function clear(): void
    {
        session()->forget($this->sessionKey);
    }

    /**
     * A cart item saved before layers existed has one sponge and one cream. Treat it as one layer.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function withLayers(array $item): array
    {
        if (! isset($item['layers'])) {
            $item['layers'] = [[
                'position' => 1,
                'sponge_id' => $item['sponge_id'] ?? null,
                'sponge_name' => $item['sponge_name'] ?? '',
                'cream_id' => $item['cream_id'] ?? null,
                'cream_name' => $item['cream_name'] ?? '',
            ]];
        }

        return $item;
    }
}
