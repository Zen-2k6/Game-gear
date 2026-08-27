<?php
declare(strict_types=1);

namespace App\Services;

use PDO;

final class CartService
{
    private ?array $cachedItems = null;

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function items(): array
    {
        if ($this->cachedItems !== null) {
            return $this->cachedItems;
        }

        $cart = $_SESSION['cart'] ?? [];
        if (!$cart) {
            return $this->cachedItems = [];
        }

        $ids = array_values(array_filter(array_map('intval', array_keys($cart)), static fn (int $id): bool => $id > 0));
        if (!$ids) {
            unset($_SESSION['cart']);
            return $this->cachedItems = [];
        }

        $marks = implode(',', array_fill(0, count($ids), '?'));
        $statement = $this->pdo->prepare("SELECT productID AS id,productName AS name,description,specifications,price,stockQuantity AS stock,imageURL AS image_url FROM Product WHERE productID IN ($marks) AND status='Active'");
        $statement->execute($ids);

        $items = [];
        $validIds = [];
        foreach ($statement->fetchAll() as $product) {
            $id = (int) $product['id'];
            $quantity = min(max(0, (int) ($cart[$id] ?? 0)), (int) $product['stock']);
            if ($quantity < 1) {
                unset($_SESSION['cart'][$id]);
                continue;
            }

            $_SESSION['cart'][$id] = $quantity;
            $validIds[] = $id;
            $product['quantity'] = $quantity;
            $product['subtotal'] = $quantity * (float) $product['price'];
            $items[] = $product;
        }

        foreach (array_keys($_SESSION['cart'] ?? []) as $id) {
            if (!in_array((int) $id, $validIds, true)) {
                unset($_SESSION['cart'][$id]);
            }
        }
        if (empty($_SESSION['cart'])) {
            unset($_SESSION['cart']);
        }

        return $this->cachedItems = $items;
    }

    public function count(): int
    {
        return array_sum(array_column($this->items(), 'quantity'));
    }

    public function total(): float
    {
        return array_sum(array_column($this->items(), 'subtotal'));
    }

    public function add(int $productId, int $quantity): bool
    {
        $statement = $this->pdo->prepare("SELECT stockQuantity FROM Product WHERE productID=? AND status='Active'");
        $statement->execute([$productId]);
        $stock = (int) ($statement->fetchColumn() ?: 0);
        if ($stock < 1) {
            return false;
        }

        $_SESSION['cart'][$productId] = min($stock, (int) ($_SESSION['cart'][$productId] ?? 0) + max(1, $quantity));
        $this->cachedItems = null;
        return true;
    }

    public function update(array $quantities): void
    {
        foreach ($quantities as $id => $quantity) {
            $productId = (int) $id;
            $requested = (int) $quantity;
            $statement = $this->pdo->prepare("SELECT stockQuantity FROM Product WHERE productID=? AND status='Active'");
            $statement->execute([$productId]);
            $stock = (int) ($statement->fetchColumn() ?: 0);

            if ($requested <= 0 || $stock < 1) {
                unset($_SESSION['cart'][$productId]);
            } else {
                $_SESSION['cart'][$productId] = min($requested, $stock);
            }
        }
        $this->cachedItems = null;
    }

    public function clear(): void
    {
        unset($_SESSION['cart']);
        $this->cachedItems = [];
    }

    public function payload(): array
    {
        return [
            'ok' => true,
            'count' => $this->count(),
            'total' => \money($this->total()),
            'items' => array_map(static fn (array $item): array => [
                'id' => (int) $item['id'],
                'name' => $item['name'],
                'image_url' => $item['image_url'],
                'quantity' => (int) $item['quantity'],
                'price' => \money($item['price']),
                'subtotal' => \money($item['subtotal']),
                'stock' => (int) $item['stock'],
            ], $this->items()),
        ];
    }
}
