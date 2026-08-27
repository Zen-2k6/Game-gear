<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\CartService;
use App\Services\OrderService;
use RuntimeException;
use Throwable;

final class CheckoutController
{
    public function __construct(private readonly CartService $cart, private readonly OrderService $orders)
    {
    }

    public function show(): void
    {
        \requireLogin();
        $items = $this->cart->items();
        if (!$items) {
            \flash('error', 'Your cart is empty.');
            \redirectTo('cart');
        }
        $total = $this->cart->total();
        \render('user/checkout', compact('items', 'total') + ['pageTitle' => 'Checkout', 'cartService' => $this->cart]);
    }

    public function place(array $input): never
    {
        \requireLogin();
        $items = $this->cart->items();
        if (!$items) {
            \flash('error', 'Your cart is empty.');
            \redirectTo('cart');
        }

        $delivery = [
            'receiver_name' => trim((string) ($input['receiver_name'] ?? '')),
            'phone' => trim((string) ($input['phone'] ?? '')),
            'address' => trim((string) ($input['address'] ?? '')),
            'payment_method' => trim((string) ($input['payment_method'] ?? '')),
            'discount_code' => trim((string) ($input['discount_code'] ?? '')),
        ];
        if ($delivery['receiver_name'] === '' || $delivery['phone'] === '' || $delivery['address'] === '' || !in_array($delivery['payment_method'], ['Credit Card Demo', 'KBZPay Mobile', 'WavePay Mobile'], true)) {
            \flash('error', 'Complete all checkout fields.');
            \redirectTo('checkout');
        }

        try {
            $order = $this->orders->place((int) \currentUser()['id'], $items, $delivery);
            $this->cart->clear();
            \flash('success', 'Payment successful' . ($order['discount'] > 0 ? ' — ' . \money($order['discount']) . ' discount applied' : '') . '. Order #' . $order['order_id'] . ' is confirmed.');
            \redirectTo('orders');
        } catch (Throwable $error) {
            \flash('error', $error instanceof RuntimeException ? $error->getMessage() : 'Order could not be completed.');
            \redirectTo('checkout');
        }
    }
}
