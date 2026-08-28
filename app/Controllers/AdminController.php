<?php
declare(strict_types=1);

namespace App\Controllers;

use PDO;
use PDOException;

final class AdminController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function index(): void
    {
        \requireAdmin();
        $tab = (string) ($_GET['tab'] ?? 'dashboard');
        $allowedTabs = ['dashboard', 'products', 'categories', 'orders', 'customers', 'feedback', 'reports'];
        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'dashboard';
        }

        $categories = $this->pdo->query('SELECT categoryID AS id,categoryName AS name FROM Category ORDER BY categoryName')->fetchAll();
        $products = $this->pdo->query("SELECT p.productID AS id,p.categoryID AS category_id,p.productName AS name,p.description,p.specifications,p.price,p.stockQuantity AS stock,p.imageURL AS image_url,c.categoryName AS category_name FROM Product p JOIN Category c ON c.categoryID=p.categoryID WHERE p.status='Active' ORDER BY p.productID DESC")->fetchAll();
        $orders = $this->pdo->query('SELECT o.orderID AS id,(o.subtotal-o.discount) AS total_amount,o.discount,o.orderStatus AS status,o.created_at,c.customerName AS customer_name,p.paymentStatus AS payment_status,p.paymentMethod AS payment_method FROM `Order` o JOIN Customer c ON c.customerID=o.customerID LEFT JOIN Payment p ON p.orderID=o.orderID ORDER BY o.created_at DESC')->fetchAll();
        $customers = $this->pdo->query("SELECT customerID AS id,customerName AS name,email,phone,address,status,created_at FROM Customer WHERE role='customer' ORDER BY created_at DESC")->fetchAll();
        $feedback = $this->pdo->query('SELECT r.reviewID AS id,r.rating,r.comment,r.created_at,c.customerName AS customer_name,c.email,p.productID AS product_id,p.productName AS product_name,p.imageURL AS image_url FROM Review r JOIN Customer c ON c.customerID=r.customerID JOIN Product p ON p.productID=r.productID ORDER BY r.updated_at DESC')->fetchAll();
        $averageFeedback = $feedback ? array_sum(array_column($feedback, 'rating')) / count($feedback) : 0;
        $stats = ['products' => count($products), 'categories' => count($categories), 'orders' => count($orders), 'customers' => (int) $this->pdo->query("SELECT COUNT(*) FROM Customer WHERE role='customer' AND status='Active'")->fetchColumn(), 'sales' => (float) $this->pdo->query("SELECT COALESCE(SUM(subtotal-discount),0) FROM `Order` WHERE orderStatus!='Cancelled'")->fetchColumn()];
        $recentOrders = array_slice($orders, 0, 5);
        $lowStock = array_values(array_filter($products, static fn (array $product): bool => (int) $product['stock'] <= 8));
        usort($lowStock, static fn (array $a, array $b): int => (int) $a['stock'] <=> (int) $b['stock']);
        $lowStock = array_slice($lowStock, 0, 5);
        $editProduct = null;
        foreach ($products as $product) {
            if (isset($_GET['edit_product']) && (int) $product['id'] === (int) $_GET['edit_product']) {
                $editProduct = $product;
            }
        }
        $editCategory = null;
        foreach ($categories as $category) {
            if (isset($_GET['edit_category']) && (int) $category['id'] === (int) $_GET['edit_category']) {
                $editCategory = $category;
            }
        }
        $orderStatusReport = $this->pdo->query('SELECT orderStatus AS label,COUNT(*) AS total FROM `Order` GROUP BY orderStatus ORDER BY total DESC')->fetchAll();
        $categorySalesReport = $this->pdo->query("SELECT c.categoryName AS label,COALESCE(SUM(CASE WHEN o.orderID IS NOT NULL THEN op.quantity*op.unitPrice ELSE 0 END),0) AS total FROM Category c LEFT JOIN Product p ON p.categoryID=c.categoryID LEFT JOIN Order_product op ON op.productID=p.productID LEFT JOIN `Order` o ON o.orderID=op.orderID AND o.orderStatus!='Cancelled' GROUP BY c.categoryID,c.categoryName ORDER BY total DESC")->fetchAll();
        $monthlySalesReport = $this->pdo->query("SELECT DATE_FORMAT(created_at,'%Y-%m') AS label,SUM(subtotal-discount) AS total FROM `Order` WHERE orderStatus!='Cancelled' AND created_at>=DATE_SUB(CURRENT_DATE,INTERVAL 5 MONTH) GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY label")->fetchAll();
        $maxMonthlySales = max(1, ...array_map(static fn (array $row): float => (float) $row['total'], $monthlySalesReport));
        $titles = ['dashboard' => 'Dashboard', 'products' => 'Products & Stock', 'categories' => 'Categories', 'orders' => 'Orders', 'customers' => 'Customers', 'feedback' => 'Customer Feedback', 'reports' => 'Reports & Analysis'];
        $pageTitle = $titles[$tab] . ' · Admin';

        \render('admin/index', compact('tab', 'categories', 'products', 'orders', 'customers', 'feedback', 'averageFeedback', 'stats', 'recentOrders', 'lowStock', 'editProduct', 'editCategory', 'orderStatusReport', 'categorySalesReport', 'monthlySalesReport', 'maxMonthlySales', 'titles', 'pageTitle'));
    }

    public function handle(string $action, array $input): never
    {
        \requireAdmin();
        $id = (int) ($input['id'] ?? 0);
        if ($action === 'admin_category_save') {
            $name = trim((string) ($input['name'] ?? ''));
            if ($name !== '') {
                $statement = $this->pdo->prepare($id ? 'UPDATE Category SET categoryName=? WHERE categoryID=?' : 'INSERT INTO Category(categoryName) VALUES(?)');
                $statement->execute($id ? [$name, $id] : [$name]);
            }
            \flash('success', 'Category saved.');
            \redirectTo('admin', ['tab' => 'categories']);
        }
        if ($action === 'admin_category_delete') {
            try {
                $this->pdo->prepare('DELETE FROM Category WHERE categoryID=?')->execute([$id]);
                \flash('success', 'Category deleted.');
            } catch (PDOException) {
                \flash('error', 'Category has products and cannot be deleted.');
            }
            \redirectTo('admin', ['tab' => 'categories']);
        }
        if ($action === 'admin_product_save') {
            $data = [(int) ($input['category_id'] ?? 0), trim((string) ($input['name'] ?? '')), trim((string) ($input['description'] ?? '')), trim((string) ($input['specifications'] ?? '')), max(0, (float) ($input['price'] ?? 0)), max(0, (int) ($input['stock'] ?? 0)), trim((string) ($input['image_url'] ?? ''))];
            if ($id) {
                $data[] = $id;
                $this->pdo->prepare('UPDATE Product SET categoryID=?,productName=?,description=?,specifications=?,price=?,stockQuantity=?,imageURL=? WHERE productID=?')->execute($data);
            } else {
                $this->pdo->prepare('INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,imageURL) VALUES(?,?,?,?,?,?,?)')->execute($data);
            }
            \flash('success', 'Product saved.');
            \redirectTo('admin', ['tab' => 'products']);
        }
        if ($action === 'admin_product_delete') {
            $this->pdo->prepare("UPDATE Product SET status='Inactive' WHERE productID=?")->execute([$id]);
            \flash('success', 'Product removed from catalog.');
            \redirectTo('admin', ['tab' => 'products']);
        }
        if ($action === 'admin_order_status') {
            $status = (string) ($input['status'] ?? '');
            if (in_array($status, ['Pending', 'Processing', 'Delivered', 'Cancelled'], true)) {
                $this->pdo->prepare('UPDATE `Order` SET orderStatus=? WHERE orderID=?')->execute([$status, $id]);
            }
            \flash('success', 'Order status updated.');
            \redirectTo('admin', ['tab' => 'orders']);
        }
        if ($action === 'admin_customer_status') {
            $status = (string) ($input['status'] ?? '');
            if (in_array($status, ['Active', 'Inactive'], true)) {
                $this->pdo->prepare("UPDATE Customer SET status=? WHERE customerID=? AND role='customer'")->execute([$status, $id]);
            }
            \flash('success', 'Customer status updated.');
            \redirectTo('admin', ['tab' => 'customers']);
        }
        \redirectTo('admin');
    }
}
