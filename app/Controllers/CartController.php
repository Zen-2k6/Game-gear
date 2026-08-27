<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\CartService;

final class CartController
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function show(): void
    {
        $items = $this->cart->items();
        $total = $this->cart->total();
        \render('user/cart', compact('items', 'total') + ['pageTitle' => 'Shopping Cart', 'cartService' => $this->cart]);
    }

    public function add(array $input): never
    {
        $added = $this->cart->add((int) ($input['product_id'] ?? 0), max(1, (int) ($input['quantity'] ?? 1)));
        if (!$added) {
            if ($this->isAjax()) {
                $this->json(['ok' => false, 'message' => 'This product is out of stock.'], 422);
            }
            \flash('error', 'This product is out of stock.');
            \redirectTo('products');
        }

        if ($this->isAjax()) {
            $payload = $this->cart->payload();
            $payload['message'] = 'Added to your cart.';
            $this->json($payload);
        }
        \flash('success', 'Product added to cart.');
        \redirectTo('cart');
    }

    public function update(array $input): never
    {
        $this->cart->update((array) ($input['quantities'] ?? []));
        if ($this->isAjax()) {
            $this->json($this->cart->payload());
        }
        \flash('success', 'Cart updated.');
        \redirectTo('cart');
    }

    private function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    private function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }
}
