<?php
declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;
use Throwable;

final class OrderService
{
    private const DISCOUNT_RATES = ['FLASH30' => .30, 'GAME10' => .10, 'WELCOME5' => .05];

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function place(int $customerId, array $items, array $delivery): array
    {
        $discountCode = strtoupper(trim((string) ($delivery['discount_code'] ?? '')));
        if ($discountCode !== '' && !isset(self::DISCOUNT_RATES[$discountCode])) {
            throw new RuntimeException('That discount code is not valid. Try FLASH30 or GAME10.');
        }

        try {
            $this->pdo->beginTransaction();
            $lockedItems = [];
            $subtotal = 0.0;

            foreach ($items as $item) {
                $lock = $this->pdo->prepare("SELECT stockQuantity AS stock,price FROM Product WHERE productID=? AND status='Active' FOR UPDATE");
                $lock->execute([$item['id']]);
                $fresh = $lock->fetch();
                if (!$fresh || (int) $fresh['stock'] < (int) $item['quantity']) {
                    throw new RuntimeException($item['name'] . ' has insufficient stock.');
                }

                $item['price'] = (float) $fresh['price'];
                $item['subtotal'] = $item['price'] * (int) $item['quantity'];
                $subtotal += $item['subtotal'];
                $lockedItems[] = $item;
            }

            $discount = round($subtotal * (self::DISCOUNT_RATES[$discountCode] ?? 0), 2);
            $payable = $subtotal - $discount;
            $notes = $discountCode !== '' ? 'Discount code: ' . $discountCode : null;

            $statement = $this->pdo->prepare("INSERT INTO `Order`(customerID,subtotal,discount,orderStatus,notes) VALUES(?,?,?,'Processing',?)");
            $statement->execute([$customerId, $subtotal, $discount, $notes]);
            $orderId = (int) $this->pdo->lastInsertId();

            foreach ($lockedItems as $item) {
                $this->pdo->prepare('INSERT INTO Order_product(orderID,productID,quantity,unitPrice) VALUES(?,?,?,?)')->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
                $this->pdo->prepare('UPDATE Product SET stockQuantity=stockQuantity-? WHERE productID=?')->execute([$item['quantity'], $item['id']]);
            }

            $this->pdo->prepare('INSERT INTO Shipment(orderID,receiverName,phone,shippingAddress,shippingFee) VALUES(?,?,?,?,0)')->execute([$orderId, $delivery['receiver_name'], $delivery['phone'], $delivery['address']]);
            $transactionId = 'GGH-' . date('Ymd') . '-' . str_pad((string) $orderId, 6, '0', STR_PAD_LEFT);
            $this->pdo->prepare("INSERT INTO Payment(orderID,paymentMethod,amount,paymentStatus,transactionID,paid_at) VALUES(?,?,?,'Paid',?,NOW())")->execute([$orderId, $delivery['payment_method'], $payable, $transactionId]);
            $this->pdo->commit();

            return ['order_id' => $orderId, 'discount' => $discount, 'total' => $payable];
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }
}
