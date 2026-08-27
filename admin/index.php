<?php
require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/includes/functions.php';
requireAdmin();

$tab = $_GET['tab'] ?? 'dashboard';
$allowedTabs = ['dashboard', 'products', 'categories', 'orders', 'customers', 'feedback', 'reports'];
if (!in_array($tab, $allowedTabs, true)) $tab = 'dashboard';

$categories = $pdo->query('SELECT categoryID AS id,categoryName AS name FROM Category ORDER BY categoryName')->fetchAll();
$products = $pdo->query("SELECT p.productID AS id,p.categoryID AS category_id,p.productName AS name,p.description,p.specifications,p.price,p.stockQuantity AS stock,p.imageURL AS image_url,c.categoryName AS category_name FROM Product p JOIN Category c ON c.categoryID=p.categoryID WHERE p.status='Active' ORDER BY p.productID DESC")->fetchAll();
$orders = $pdo->query('SELECT o.orderID AS id,(o.subtotal-o.discount) AS total_amount,o.discount,o.orderStatus AS status,o.created_at,c.customerName AS customer_name,p.paymentStatus AS payment_status,p.paymentMethod AS payment_method FROM `Order` o JOIN Customer c ON c.customerID=o.customerID LEFT JOIN Payment p ON p.orderID=o.orderID ORDER BY o.created_at DESC')->fetchAll();
$customers = $pdo->query("SELECT customerID AS id,customerName AS name,email,phone,address,status,created_at FROM Customer WHERE role='customer' ORDER BY created_at DESC")->fetchAll();
$feedback = $pdo->query('SELECT r.reviewID AS id,r.rating,r.comment,r.created_at,c.customerName AS customer_name,c.email,p.productID AS product_id,p.productName AS product_name,p.imageURL AS image_url FROM Review r JOIN Customer c ON c.customerID=r.customerID JOIN Product p ON p.productID=r.productID ORDER BY r.updated_at DESC')->fetchAll();
$averageFeedback = $feedback ? array_sum(array_column($feedback, 'rating')) / count($feedback) : 0;
$stats = [
    'products' => count($products),
    'categories' => count($categories),
    'orders' => count($orders),
    'customers' => (int)$pdo->query("SELECT COUNT(*) FROM Customer WHERE role='customer' AND status='Active'")->fetchColumn(),
    'sales' => (float)$pdo->query("SELECT COALESCE(SUM(subtotal-discount),0) FROM `Order` WHERE orderStatus!='Cancelled'")->fetchColumn(),
];
$recentOrders = array_slice($orders, 0, 5);
$lowStock = array_values(array_filter($products, fn($product) => (int)$product['stock'] <= 8));
usort($lowStock, fn($a, $b) => (int)$a['stock'] <=> (int)$b['stock']);
$lowStock = array_slice($lowStock, 0, 5);

$editProduct = null;
if (isset($_GET['edit_product'])) {
    foreach ($products as $product) if ((int)$product['id'] === (int)$_GET['edit_product']) $editProduct = $product;
}
$editCategory = null;
if (isset($_GET['edit_category'])) {
    foreach ($categories as $category) if ((int)$category['id'] === (int)$_GET['edit_category']) $editCategory = $category;
}
$orderStatusReport = $pdo->query('SELECT orderStatus AS label,COUNT(*) AS total FROM `Order` GROUP BY orderStatus ORDER BY total DESC')->fetchAll();
$categorySalesReport = $pdo->query("SELECT c.categoryName AS label,COALESCE(SUM(CASE WHEN o.orderID IS NOT NULL THEN op.quantity*op.unitPrice ELSE 0 END),0) AS total FROM Category c LEFT JOIN Product p ON p.categoryID=c.categoryID LEFT JOIN Order_product op ON op.productID=p.productID LEFT JOIN `Order` o ON o.orderID=op.orderID AND o.orderStatus!='Cancelled' GROUP BY c.categoryID,c.categoryName ORDER BY total DESC")->fetchAll();
$monthlySalesReport = $pdo->query("SELECT DATE_FORMAT(created_at,'%Y-%m') AS label,SUM(subtotal-discount) AS total FROM `Order` WHERE orderStatus!='Cancelled' AND created_at>=DATE_SUB(CURRENT_DATE,INTERVAL 5 MONTH) GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY label")->fetchAll();
$maxMonthlySales = max(1, ...array_map(fn($row) => (float)$row['total'], $monthlySalesReport));

$titles = ['dashboard' => 'Dashboard', 'products' => 'Products & Stock', 'categories' => 'Categories', 'orders' => 'Orders', 'customers' => 'Customers', 'feedback' => 'Customer Feedback', 'reports' => 'Reports & Analysis'];
$pageTitle = $titles[$tab] . ' · Admin';
require __DIR__ . '/partials/header.php';
?>
<div class="admin-app">
    <button class="admin-sidebar-toggle" type="button" aria-label="Toggle admin menu">☰</button>
    <div class="admin-sidebar-backdrop"></div>
    <aside class="admin-sidebar">
        <div class="admin-brand"><span>GG</span><div><strong>GameGear</strong><small>ADMIN CONSOLE</small></div></div>
        <nav class="admin-nav" aria-label="Admin navigation">
            <p>WORKSPACE</p>
            <a class="<?= $tab === 'dashboard' ? 'active' : '' ?>" href="index.php"><span>▦</span> Overview</a>
            <a class="<?= $tab === 'products' ? 'active' : '' ?>" href="?tab=products"><span>◇</span> Products <b><?= count($products) ?></b></a>
            <a class="<?= $tab === 'categories' ? 'active' : '' ?>" href="?tab=categories"><span>⌘</span> Categories</a>
            <a class="<?= $tab === 'orders' ? 'active' : '' ?>" href="?tab=orders"><span>▤</span> Orders <b><?= count($orders) ?></b></a>
            <a class="<?= $tab === 'customers' ? 'active' : '' ?>" href="?tab=customers"><span>◎</span> Customers <b><?= count($customers) ?></b></a>
            <a class="<?= $tab === 'feedback' ? 'active' : '' ?>" href="?tab=feedback"><span>★</span> Feedback <b><?= count($feedback) ?></b></a>
            <a class="<?= $tab === 'reports' ? 'active' : '' ?>" href="?tab=reports"><span>↗</span> Reports</a>
            <p>QUICK LINKS</p>
            <a href="<?= appUrl('user/index.php') ?>"><span>↗</span> View storefront</a>
        </nav>
        <div class="admin-account">
            <div class="admin-user"><span><?= strtoupper(substr(currentUser()['name'], 0, 1)) ?></span><div><strong><?= e(currentUser()['name']) ?></strong><small>Administrator</small></div></div>
            <form action="<?= appUrl('actions/index.php') ?>" method="post"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="logout"><button class="admin-logout" type="submit">Log out ↗</button></form>
        </div>
    </aside>

    <section class="admin-main">
        <?php if (!empty($_SESSION['flash'])): $notice = $_SESSION['flash']; unset($_SESSION['flash']); ?>
            <div class="alert <?= e($notice['type']) ?> admin-alert"><?= e($notice['message']) ?></div>
        <?php endif; ?>
        <header class="admin-topbar">
            <div><p><?= date('l, F j') ?></p><h1><?= e($titles[$tab]) ?></h1></div>
            <?php if ($tab === 'products'): ?><a class="button" href="?tab=products#product-form">+ Add product</a><?php elseif ($tab === 'categories'): ?><a class="button" href="?tab=categories#category-form">+ Add category</a><?php endif; ?>
        </header>

        <?php if ($tab === 'dashboard'): ?>
            <section class="admin-welcome"><div><p class="eyebrow">STORE OVERVIEW</p><h2>Welcome back, <?= e(explode(' ', currentUser()['name'])[0]) ?>.</h2><p>Here’s what’s happening across GameGear Hub today.</p></div><a href="?tab=products" class="text-link">Manage catalog →</a></section>
            <section class="admin-metrics">
                <article><span class="metric-icon lime">◇</span><div><small>Total products</small><strong><?= $stats['products'] ?></strong><p><?= count($lowStock) ?> need stock attention</p></div></article>
                <article><span class="metric-icon purple">▤</span><div><small>Total orders</small><strong><?= $stats['orders'] ?></strong><p><?= count(array_filter($orders, fn($o) => $o['status'] === 'Processing')) ?> currently processing</p></div></article>
                <article><span class="metric-icon blue">◎</span><div><small>Customers</small><strong><?= $stats['customers'] ?></strong><p><a href="?tab=customers">Active customer accounts</a></p></div></article>
                <article><span class="metric-icon amber">↗</span><div><small>Total revenue</small><strong><?= money($stats['sales']) ?></strong><p>Excluding cancelled orders</p></div></article>
            </section>
            <section class="admin-dashboard-grid">
                <article class="admin-panel recent-orders"><div class="panel-heading"><div><h2>Recent orders</h2><p>Latest customer purchases</p></div><a href="?tab=orders">View all →</a></div><div class="admin-table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead><tbody>
                    <?php foreach ($recentOrders as $order): ?><tr><td><strong>#<?= $order['id'] ?></strong><small><?= date('M j, Y', strtotime($order['created_at'])) ?></small></td><td><?= e($order['customer_name']) ?></td><td><?= money($order['total_amount']) ?></td><td><span class="order-pill <?= strtolower($order['status']) ?>"><?= e($order['status']) ?></span></td></tr><?php endforeach; ?>
                    <?php if (!$recentOrders): ?><tr><td colspan="4" class="table-empty">No orders yet.</td></tr><?php endif; ?>
                </tbody></table></div></article>
                <article class="admin-panel stock-panel"><div class="panel-heading"><div><h2>Stock watch</h2><p>Products with 8 or fewer</p></div><a href="?tab=products">Manage →</a></div><div class="stock-list">
                    <?php foreach ($lowStock as $product): ?><a href="?tab=products&edit_product=<?= $product['id'] ?>#product-form"><img src="<?= e($product['image_url']) ?>" alt=""><div><strong><?= e($product['name']) ?></strong><small><?= e($product['category_name']) ?></small></div><b class="<?= $product['stock'] <= 4 ? 'critical' : '' ?>"><?= $product['stock'] ?> left</b></a><?php endforeach; ?>
                    <?php if (!$lowStock): ?><p class="table-empty">Stock levels look healthy.</p><?php endif; ?>
                </div></article>
            </section>

        <?php elseif ($tab === 'products'): ?>
            <section class="admin-work-grid">
                <article class="admin-panel admin-form-panel" id="product-form"><div class="panel-heading"><div><h2><?= $editProduct ? 'Edit product' : 'New product' ?></h2><p><?= $editProduct ? 'Update catalog details' : 'Add gear to the storefront' ?></p></div></div>
                    <form action="<?= appUrl('actions/index.php') ?>" method="post"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="admin_product_save"><input type="hidden" name="id" value="<?= $editProduct['id'] ?? '' ?>">
                        <label>Category<select name="category_id" required><?php foreach ($categories as $category): ?><option value="<?= $category['id'] ?>" <?= ($editProduct['category_id'] ?? 0) == $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></label>
                        <label>Product name<input name="name" required value="<?= e($editProduct['name'] ?? '') ?>"></label><label>Description<textarea name="description" required rows="3"><?= e($editProduct['description'] ?? '') ?></textarea></label><label>Specifications<textarea name="specifications" required rows="4"><?= e($editProduct['specifications'] ?? '') ?></textarea></label>
                        <div class="two"><label>Price (MMK)<input type="number" name="price" min="0" required value="<?= e((string)($editProduct['price'] ?? '')) ?>"></label><label>Stock<input type="number" name="stock" min="0" required value="<?= e((string)($editProduct['stock'] ?? '')) ?>"></label></div><label>Image URL<input type="url" name="image_url" required value="<?= e($editProduct['image_url'] ?? '') ?>"></label>
                        <div class="admin-form-actions"><button><?= $editProduct ? 'Update product' : 'Create product' ?></button><?php if ($editProduct): ?><a class="button secondary" href="?tab=products">Cancel</a><?php endif; ?></div>
                    </form>
                </article>
                <article class="admin-panel catalog-panel"><div class="panel-heading"><div><h2>Product catalog</h2><p><?= count($products) ?> active products</p></div></div><div class="admin-table-wrap"><table><thead><tr><th>Product</th><th>Price</th><th>Stock</th><th></th></tr></thead><tbody><?php foreach ($products as $product): ?><tr>
                    <td><div class="admin-product"><img src="<?= e($product['image_url']) ?>" alt=""><div><strong><?= e($product['name']) ?></strong><small><?= e($product['category_name']) ?></small></div></div></td><td><?= money($product['price']) ?></td><td><span class="stock-count <?= $product['stock'] <= 4 ? 'critical' : '' ?>"><?= $product['stock'] ?></span></td>
                    <td class="table-actions"><a href="?tab=products&edit_product=<?= $product['id'] ?>#product-form">Edit</a><form action="<?= appUrl('actions/index.php') ?>" method="post" data-confirm="Remove this product?"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="admin_product_delete"><input type="hidden" name="id" value="<?= $product['id'] ?>"><button class="danger link-button">Delete</button></form></td>
                </tr><?php endforeach; ?></tbody></table></div></article>
            </section>

        <?php elseif ($tab === 'categories'): ?>
            <section class="admin-work-grid categories-grid"><article class="admin-panel admin-form-panel" id="category-form"><div class="panel-heading"><div><h2><?= $editCategory ? 'Edit category' : 'New category' ?></h2><p>Organize your product catalog</p></div></div><form action="<?= appUrl('actions/index.php') ?>" method="post"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="admin_category_save"><input type="hidden" name="id" value="<?= $editCategory['id'] ?? '' ?>"><label>Category name<input name="name" required placeholder="e.g. Gaming Monitors" value="<?= e($editCategory['name'] ?? '') ?>"></label><div class="admin-form-actions"><button><?= $editCategory ? 'Update category' : 'Create category' ?></button><?php if($editCategory): ?><a class="button secondary" href="?tab=categories">Cancel</a><?php endif; ?></div></form></article>
                <article class="admin-panel"><div class="panel-heading"><div><h2>Categories</h2><p><?= count($categories) ?> catalog groups</p></div></div><div class="category-admin-list"><?php foreach ($categories as $index => $category): ?><div><span><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></span><strong><?= e($category['name']) ?></strong><span class="category-actions"><a href="?tab=categories&edit_category=<?= $category['id'] ?>#category-form">Edit</a><form action="<?= appUrl('actions/index.php') ?>" method="post" data-confirm="Delete this category?"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="admin_category_delete"><input type="hidden" name="id" value="<?= $category['id'] ?>"><button class="danger link-button">Delete</button></form></span></div><?php endforeach; ?></div></article>
            </section>

        <?php elseif ($tab === 'orders'): ?>
            <article class="admin-panel orders-panel"><div class="panel-heading"><div><h2>All orders</h2><p>Track payment and fulfilment status</p></div><span><?= count($orders) ?> total</span></div><div class="admin-table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Order status</th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr>
                <td><strong>#<?= $order['id'] ?></strong><small><?= date('M j, Y · g:i A', strtotime($order['created_at'])) ?></small></td><td><?= e($order['customer_name']) ?></td><td><?= money($order['total_amount']) ?><?php if((float)$order['discount']>0): ?><small><?= money($order['discount']) ?> saved</small><?php endif; ?></td><td><span class="payment-state"><?= e($order['payment_status'] ?? 'Pending') ?></span><small><?= e($order['payment_method'] ?? '') ?></small></td><td><form action="<?= appUrl('actions/index.php') ?>" method="post"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="admin_order_status"><input type="hidden" name="id" value="<?= $order['id'] ?>"><select name="status" onchange="this.form.submit()" aria-label="Order status"><?php foreach (['Pending','Processing','Delivered','Cancelled'] as $status): ?><option <?= $order['status'] === $status ? 'selected' : '' ?>><?= $status ?></option><?php endforeach; ?></select></form></td>
            </tr><?php endforeach; ?><?php if (!$orders): ?><tr><td colspan="5" class="table-empty">No orders have been placed.</td></tr><?php endif; ?></tbody></table></div></article>

        <?php elseif ($tab === 'customers'): ?>
            <article class="admin-panel customers-panel"><div class="panel-heading"><div><h2>Customer accounts</h2><p>View contact details and control account access</p></div><span><?= count($customers) ?> total</span></div><div class="admin-table-wrap"><table><thead><tr><th>Customer</th><th>Contact</th><th>Address</th><th>Joined</th><th>Status</th></tr></thead><tbody>
                <?php foreach($customers as $customer): ?><tr><td><strong><?= e($customer['name']) ?></strong></td><td><?= e($customer['email']) ?><small><?= e($customer['phone']) ?></small></td><td class="customer-address"><?= e($customer['address'] ?: 'Not provided') ?></td><td><?= date('M j, Y',strtotime($customer['created_at'])) ?></td><td><form action="<?= appUrl('actions/index.php') ?>" method="post"><input type="hidden" name="csrf" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="admin_customer_status"><input type="hidden" name="id" value="<?= $customer['id'] ?>"><select name="status" onchange="this.form.submit()" aria-label="Customer status"><option <?= $customer['status']==='Active'?'selected':'' ?>>Active</option><option <?= $customer['status']==='Inactive'?'selected':'' ?>>Inactive</option></select></form></td></tr><?php endforeach; ?>
                <?php if(!$customers): ?><tr><td colspan="5" class="table-empty">No customer accounts yet.</td></tr><?php endif; ?>
            </tbody></table></div></article>

        <?php elseif ($tab === 'feedback'): ?>
            <section class="feedback-summary"><article><small>Total reviews</small><strong><?= count($feedback) ?></strong></article><article><small>Average rating</small><strong><?= $feedback ? number_format($averageFeedback,1).' / 5' : '—' ?></strong></article><article><small>Five-star reviews</small><strong><?= count(array_filter($feedback,fn($review)=>(int)$review['rating']===5)) ?></strong></article></section>
            <article class="admin-panel feedback-panel"><div class="panel-heading"><div><h2>Ratings and comments</h2><p>Feedback submitted from product detail pages</p></div><span><?= count($feedback) ?> total</span></div><div class="admin-table-wrap"><table><thead><tr><th>Customer</th><th>Product</th><th>Rating</th><th>Comment</th><th>Date</th></tr></thead><tbody>
                <?php foreach($feedback as $review): ?><tr><td><strong><?= e($review['customer_name']) ?></strong><small><?= e($review['email']) ?></small></td><td><a class="feedback-product" href="<?= appUrl('user/product.php?id='.$review['product_id'].'#reviews') ?>"><img src="<?= e($review['image_url']) ?>" alt=""><span><?= e($review['product_name']) ?></span></a></td><td><span class="review-stars" aria-label="<?= (int)$review['rating'] ?> out of 5"><?= str_repeat('★',(int)$review['rating']).str_repeat('☆',5-(int)$review['rating']) ?></span></td><td class="feedback-comment"><?= nl2br(e($review['comment'])) ?></td><td><?= date('M j, Y',strtotime($review['created_at'])) ?></td></tr><?php endforeach; ?>
                <?php if(!$feedback): ?><tr><td colspan="5" class="table-empty">No customer feedback yet. Reviews will appear here after customers submit them.</td></tr><?php endif; ?>
            </tbody></table></div></article>

        <?php else: ?>
            <section class="report-grid">
                <article class="admin-panel report-chart"><div class="panel-heading"><div><h2>Monthly revenue</h2><p>Non-cancelled orders from the last six months</p></div></div><div class="bar-chart"><?php foreach($monthlySalesReport as $row): ?><div><span style="height:<?= max(4,round(((float)$row['total']/$maxMonthlySales)*100)) ?>%" title="<?= money($row['total']) ?>"></span><small><?= date('M y',strtotime($row['label'].'-01')) ?></small><b><?= money($row['total']) ?></b></div><?php endforeach; ?><?php if(!$monthlySalesReport): ?><p class="table-empty">Revenue will appear after orders are placed.</p><?php endif; ?></div></article>
                <article class="admin-panel"><div class="panel-heading"><div><h2>Order status</h2><p>Fulfilment overview</p></div></div><div class="report-list"><?php foreach($orderStatusReport as $row): ?><div><span class="order-pill <?= strtolower($row['label']) ?>"><?= e($row['label']) ?></span><strong><?= (int)$row['total'] ?></strong></div><?php endforeach; ?><?php if(!$orderStatusReport): ?><p class="table-empty">No orders to analyse.</p><?php endif; ?></div></article>
                <article class="admin-panel report-wide"><div class="panel-heading"><div><h2>Sales by category</h2><p>Gross product sales by catalog group</p></div></div><div class="admin-table-wrap"><table><thead><tr><th>Category</th><th>Sales</th></tr></thead><tbody><?php foreach($categorySalesReport as $row): ?><tr><td><?= e($row['label']) ?></td><td><strong><?= money($row['total']) ?></strong></td></tr><?php endforeach; ?></tbody></table></div></article>
            </section>
        <?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
