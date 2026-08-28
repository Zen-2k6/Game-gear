<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\CartService;
use PDO;

final class StorefrontController
{
    public function __construct(private readonly PDO $pdo, private readonly CartService $cart)
    {
    }

    public function home(): void
    {
        $categories = $this->pdo->query("SELECT c.categoryID AS id,c.categoryName AS name,(SELECT p.imageURL FROM Product p WHERE p.categoryID=c.categoryID AND p.status='Active' ORDER BY p.productID DESC LIMIT 1) AS image_url FROM Category c ORDER BY c.categoryID")->fetchAll();
        $categoryLookup = [];
        foreach ($categories as $category) {
            $categoryLookup[strtolower($category['name'])] = $category;
        }
        $definitions = [
            ['database' => 'gaming keyboards', 'name' => 'Gaming Keyboard', 'fallback' => 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?auto=format&fit=crop&w=900&q=80'],
            ['database' => 'gaming mice', 'name' => 'Gaming Mouse', 'fallback' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?auto=format&fit=crop&w=900&q=80'],
            ['database' => 'headsets', 'name' => 'Headset', 'fallback' => 'https://images.unsplash.com/photo-1599669454699-248893623440?auto=format&fit=crop&w=900&q=80'],
            ['database' => 'controllers', 'name' => 'Controller', 'fallback' => 'https://images.unsplash.com/photo-1592840496694-26d035b52b48?auto=format&fit=crop&w=900&q=80'],
            ['database' => 'rgb cooling', 'name' => 'RGB Fan', 'fallback' => 'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&w=900&q=80'],
            ['database' => 'gaming chairs', 'name' => 'Gaming Chair', 'fallback' => 'https://images.unsplash.com/photo-1598550476439-6847785fcea6?auto=format&fit=crop&w=900&q=80'],
        ];
        $categoryCards = [];
        foreach ($definitions as $definition) {
            $stored = $categoryLookup[$definition['database']] ?? null;
            $categoryCards[] = ['id' => $stored['id'] ?? 0, 'name' => $definition['name'], 'image_url' => $stored['image_url'] ?? $definition['fallback']];
        }

        $query = "SELECT p.productID AS id,p.productName AS name,p.price,p.stockQuantity AS stock,p.imageURL AS image_url,c.categoryName AS category_name,(SELECT COALESCE(SUM(op.quantity),0) FROM Order_product op JOIN `Order` o ON o.orderID=op.orderID WHERE op.productID=p.productID AND o.orderStatus!='Cancelled') AS sold,(SELECT COALESCE(AVG(r.rating),0) FROM Review r WHERE r.productID=p.productID) AS average_rating,(SELECT COUNT(*) FROM Review r WHERE r.productID=p.productID) AS review_count FROM Product p JOIN Category c ON c.categoryID=p.categoryID WHERE p.status='Active'";
        $allProducts = $this->pdo->query($query)->fetchAll();
        $bestSellers = $allProducts;
        usort($bestSellers, static fn (array $a, array $b): int => [(int) $b['sold'], (int) $b['id']] <=> [(int) $a['sold'], (int) $a['id']]);
        $newArrivals = $allProducts;
        usort($newArrivals, static fn (array $a, array $b): int => (int) $b['id'] <=> (int) $a['id']);
        $popularProducts = $allProducts;
        usort($popularProducts, static fn (array $a, array $b): int => [(int) $b['review_count'], (float) $b['average_rating'], (int) $b['sold']] <=> [(int) $a['review_count'], (float) $a['average_rating'], (int) $a['sold']]);
        $featuredGroups = ['Best Selling Products' => array_slice($bestSellers, 0, 3), 'New Arrivals' => array_slice($newArrivals, 0, 3), 'Popular Gaming Accessories' => array_slice($popularProducts, 0, 3)];
        $promotionProducts = array_slice($bestSellers, 0, 3);
        $reviews = $this->pdo->query("SELECT r.rating,r.comment,c.customerName AS customer_name,p.productName AS product_name FROM Review r JOIN Customer c ON c.customerID=r.customerID JOIN Product p ON p.productID=r.productID WHERE r.comment IS NOT NULL AND r.comment!='' ORDER BY r.updated_at DESC LIMIT 3")->fetchAll();
        if (!$reviews) {
            $reviews = [['rating' => 5, 'comment' => 'Excellent gaming mouse with fast response.', 'customer_name' => 'GameGear Customer', 'product_name' => 'Gaming Mouse']];
        }

        \render('user/home', compact('categoryCards', 'featuredGroups', 'promotionProducts', 'reviews') + ['pageTitle' => 'Home', 'cartService' => $this->cart]);
    }

    public function products(): void
    {
        $search = trim((string) ($_GET['search'] ?? ''));
        $categoryId = (int) ($_GET['category'] ?? 0);
        $categories = $this->pdo->query('SELECT categoryID AS id,categoryName AS name FROM Category ORDER BY categoryName')->fetchAll();
        $sql = "SELECT p.productID AS id,p.categoryID AS category_id,p.productName AS name,p.description,p.specifications,p.price,p.stockQuantity AS stock,p.imageURL AS image_url,c.categoryName AS category_name,(SELECT COALESCE(AVG(r.rating),0) FROM Review r WHERE r.productID=p.productID) AS average_rating,(SELECT COUNT(*) FROM Review r WHERE r.productID=p.productID) AS review_count FROM Product p JOIN Category c ON c.categoryID=p.categoryID WHERE p.status='Active'";
        $values = [];
        if ($search !== '') {
            $sql .= ' AND (p.productName LIKE ? OR p.description LIKE ? OR c.categoryName LIKE ?)';
            $term = '%' . $search . '%';
            array_push($values, $term, $term, $term);
        }
        if ($categoryId > 0) {
            $sql .= ' AND p.categoryID=?';
            $values[] = $categoryId;
        }
        $sql .= ' ORDER BY p.created_at DESC';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);
        $products = $statement->fetchAll();
        \render('user/products', compact('search', 'categoryId', 'categories', 'products') + ['pageTitle' => 'Products', 'cartService' => $this->cart]);
    }

    public function product(): void
    {
        $statement = $this->pdo->prepare("SELECT p.productID AS id,p.categoryID AS category_id,p.productName AS name,p.description,p.specifications,p.price,p.stockQuantity AS stock,p.imageURL AS image_url,c.categoryName AS category_name FROM Product p JOIN Category c ON c.categoryID=p.categoryID WHERE p.productID=? AND p.status='Active'");
        $statement->execute([(int) ($_GET['id'] ?? 0)]);
        $product = $statement->fetch();
        if (!$product) {
            http_response_code(404);
            exit('Product not found.');
        }
        $reviewStatement = $this->pdo->prepare('SELECT r.reviewID,r.rating,r.comment,r.created_at,c.customerName AS customer_name,r.customerID FROM Review r JOIN Customer c ON c.customerID=r.customerID WHERE r.productID=? ORDER BY r.updated_at DESC');
        $reviewStatement->execute([$product['id']]);
        $reviews = $reviewStatement->fetchAll();
        $ratingCount = count($reviews);
        $averageRating = $ratingCount ? array_sum(array_column($reviews, 'rating')) / $ratingCount : 0;
        $myReview = null;
        if (\currentUser()) {
            foreach ($reviews as $review) {
                if ((int) $review['customerID'] === (int) \currentUser()['id']) {
                    $myReview = $review;
                }
            }
        }
        \render('user/product', compact('product', 'reviews', 'ratingCount', 'averageRating', 'myReview') + ['pageTitle' => $product['name'], 'cartService' => $this->cart]);
    }

    public function about(): void
    {
        $aboutStats = [
            'products' => (int) $this->pdo->query("SELECT COUNT(*) FROM Product WHERE status='Active'")->fetchColumn(),
            'categories' => (int) $this->pdo->query('SELECT COUNT(*) FROM Category')->fetchColumn(),
            'customers' => (int) $this->pdo->query("SELECT COUNT(*) FROM Customer WHERE role='customer' AND status='Active'")->fetchColumn(),
        ];
        \render('user/about', compact('aboutStats') + ['pageTitle' => 'About Us', 'cartService' => $this->cart]);
    }

    public function contact(): void
    {
        \render('user/contact', ['pageTitle' => 'Contact Us', 'cartService' => $this->cart]);
    }

    public function review(array $input): never
    {
        \requireLogin();
        $productId = (int) ($input['product_id'] ?? 0);
        $rating = (int) ($input['rating'] ?? 0);
        $comment = trim((string) ($input['comment'] ?? ''));
        $exists = $this->pdo->prepare("SELECT COUNT(*) FROM Product WHERE productID=? AND status='Active'");
        $exists->execute([$productId]);
        if (!$exists->fetchColumn() || $rating < 1 || $rating > 5 || $comment === '') {
            \flash('error', 'Choose a rating and write a review.');
            \redirectTo('product', ['id' => $productId], 'reviews');
        }

        $review = $this->pdo->prepare('SELECT reviewID FROM Review WHERE customerID=? AND productID=? LIMIT 1');
        $review->execute([\currentUser()['id'], $productId]);
        $reviewId = $review->fetchColumn();
        if ($reviewId) {
            $this->pdo->prepare('UPDATE Review SET rating=?,comment=? WHERE reviewID=?')->execute([$rating, $comment, $reviewId]);
            $message = 'Your review was updated.';
        } else {
            $this->pdo->prepare('INSERT INTO Review(customerID,productID,rating,comment) VALUES(?,?,?,?)')->execute([\currentUser()['id'], $productId, $rating, $comment]);
            $message = 'Thanks for reviewing this product.';
        }
        \flash('success', $message);
        \redirectTo('product', ['id' => $productId], 'reviews');
    }
}
