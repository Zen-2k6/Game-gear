<?php
require dirname(__DIR__) . '/config/database.php'; require dirname(__DIR__) . '/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('user/index.php');
verifyCsrf(); $action=$_POST['action'] ?? '';

if ($action === 'register') {
    $name=trim($_POST['name'] ?? ''); $email=strtolower(trim($_POST['email'] ?? '')); $phone=trim($_POST['phone'] ?? ''); $address=trim($_POST['address'] ?? ''); $password=$_POST['password'] ?? '';
    if(!$name || !filter_var($email,FILTER_VALIDATE_EMAIL) || !$phone || strlen($password)<8){flash('error','Enter valid details. Password needs at least 8 characters.');redirect('user/register.php');}
    try{$s=$pdo->prepare('INSERT INTO Customer(customerName,email,phone,password,address) VALUES(?,?,?,?,?)');$s->execute([$name,$email,$phone,password_hash($password,PASSWORD_DEFAULT),$address]);flash('success','Account created. Please log in.');redirect('user/login.php');}
    catch(PDOException $e){flash('error','That email address is already registered.');redirect('user/register.php');}
}
if ($action === 'login') {
    $s=$pdo->prepare("SELECT * FROM Customer WHERE email=? AND status='Active'");$s->execute([strtolower(trim($_POST['email']??''))]);$user=$s->fetch();
    if(!$user || !password_verify($_POST['password']??'',$user['password'])){flash('error','Incorrect email or password.');redirect('user/login.php');}
    session_regenerate_id(true);$_SESSION['user']=['id'=>$user['customerID'],'name'=>$user['customerName'],'email'=>$user['email'],'phone'=>$user['phone'],'address'=>$user['address'],'role'=>$user['role']];flash('success','Welcome back, '.$user['customerName'].'!');redirect($user['role']==='admin'?'admin/index.php':'user/index.php');
}
if ($action === 'logout') { $_SESSION=[];session_destroy();redirect('user/index.php'); }
if ($action === 'update_profile') {
    requireLogin();
    $name=trim($_POST['name']??'');$email=strtolower(trim($_POST['email']??''));$phone=trim($_POST['phone']??'');$address=trim($_POST['address']??'');$password=$_POST['password']??'';
    if(!$name||!filter_var($email,FILTER_VALIDATE_EMAIL)||!$phone||($password!==''&&strlen($password)<8)){flash('error','Enter valid profile details. A new password needs at least 8 characters.');redirect('user/profile.php');}
    try{
        if($password!==''){$s=$pdo->prepare('UPDATE Customer SET customerName=?,email=?,phone=?,address=?,password=? WHERE customerID=?');$s->execute([$name,$email,$phone,$address,password_hash($password,PASSWORD_DEFAULT),currentUser()['id']]);}
        else{$s=$pdo->prepare('UPDATE Customer SET customerName=?,email=?,phone=?,address=? WHERE customerID=?');$s->execute([$name,$email,$phone,$address,currentUser()['id']]);}
        $_SESSION['user']=array_merge(currentUser(),['name'=>$name,'email'=>$email,'phone'=>$phone,'address'=>$address]);flash('success','Profile updated.');
    }catch(PDOException $e){flash('error','That email address is already used by another account.');}
    redirect('user/profile.php');
}
if ($action === 'add_cart') {
    $id=(int)($_POST['product_id']??0);$quantity=max(1,(int)($_POST['quantity']??1));$s=$pdo->prepare("SELECT stockQuantity FROM Product WHERE productID=? AND status='Active'");$s->execute([$id]);$stock=(int)($s->fetchColumn()?:0);
    $isAjax=strtolower($_SERVER['HTTP_X_REQUESTED_WITH']??'')==='xmlhttprequest';
    if($stock<1){
        if($isAjax){header('Content-Type: application/json');http_response_code(422);echo json_encode(['ok'=>false,'message'=>'This product is out of stock.']);exit;}
        flash('error','This product is out of stock.');redirect('user/products.php');
    }
    $_SESSION['cart'][$id]=min($stock,($_SESSION['cart'][$id]??0)+$quantity);
    if($isAjax){
        $items=cartProducts($pdo);$total=array_sum(array_column($items,'subtotal'));
        header('Content-Type: application/json');echo json_encode(['ok'=>true,'message'=>'Added to your cart.','count'=>cartCount(),'total'=>money($total),'items'=>array_map(fn($item)=>['id'=>(int)$item['id'],'name'=>$item['name'],'image_url'=>$item['image_url'],'quantity'=>(int)$item['quantity'],'price'=>money($item['price']),'stock'=>(int)$item['stock']],$items)]);exit;
    }
    flash('success','Product added to cart.');redirect($_SERVER['HTTP_REFERER']??'index.php');
}
if ($action === 'update_cart') {
    foreach($_POST['quantities']??[] as $id=>$quantity){$id=(int)$id;$quantity=(int)$quantity;$s=$pdo->prepare("SELECT stockQuantity FROM Product WHERE productID=? AND status='Active'");$s->execute([$id]);$stock=(int)($s->fetchColumn()?:0);if($quantity<=0||$stock<1)unset($_SESSION['cart'][$id]);else $_SESSION['cart'][$id]=min($quantity,$stock);}
    if(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']??'')==='xmlhttprequest'){$items=cartProducts($pdo);$total=array_sum(array_column($items,'subtotal'));header('Content-Type: application/json');echo json_encode(['ok'=>true,'count'=>cartCount(),'total'=>money($total),'items'=>array_map(fn($item)=>['id'=>(int)$item['id'],'name'=>$item['name'],'image_url'=>$item['image_url'],'quantity'=>(int)$item['quantity'],'price'=>money($item['price']),'stock'=>(int)$item['stock']],$items)]);exit;}
    flash('success','Cart updated.');redirect('user/cart.php');
}
if ($action === 'add_review') {
    requireLogin();$productId=(int)($_POST['product_id']??0);$rating=(int)($_POST['rating']??0);$comment=trim($_POST['comment']??'');
    $exists=$pdo->prepare("SELECT COUNT(*) FROM Product WHERE productID=? AND status='Active'");$exists->execute([$productId]);
    if(!$exists->fetchColumn()||$rating<1||$rating>5||$comment===''){flash('error','Choose a rating and write a review.');redirect('user/product.php?id='.$productId.'#reviews');}
    $review=$pdo->prepare('SELECT reviewID FROM Review WHERE customerID=? AND productID=? LIMIT 1');$review->execute([currentUser()['id'],$productId]);$reviewId=$review->fetchColumn();
    if($reviewId){$pdo->prepare('UPDATE Review SET rating=?,comment=? WHERE reviewID=?')->execute([$rating,$comment,$reviewId]);$message='Your review was updated.';}
    else{$pdo->prepare('INSERT INTO Review(customerID,productID,rating,comment) VALUES(?,?,?,?)')->execute([currentUser()['id'],$productId,$rating,$comment]);$message='Thanks for reviewing this product.';}
    flash('success',$message);redirect('user/product.php?id='.$productId.'#reviews');
}
if ($action === 'checkout') {
    requireLogin();$items=cartProducts($pdo);if(!$items){flash('error','Your cart is empty.');redirect('user/cart.php');}$receiver=trim($_POST['receiver_name']??'');$phone=trim($_POST['phone']??'');$address=trim($_POST['address']??'');$method=trim($_POST['payment_method']??'');$discountCode=strtoupper(trim($_POST['discount_code']??''));
    $discountRates=['FLASH30'=>.30,'GAME10'=>.10,'WELCOME5'=>.05];
    if(!$receiver||!$phone||!$address||!in_array($method,['Credit Card Demo','KBZPay Mobile','WavePay Mobile'],true)){flash('error','Complete all checkout fields.');redirect('user/checkout.php');}
    if($discountCode!==''&&!isset($discountRates[$discountCode])){flash('error','That discount code is not valid. Try FLASH30 or GAME10.');redirect('user/checkout.php');}
    try{$pdo->beginTransaction();$total=0;foreach($items as $item){$lock=$pdo->prepare('SELECT stockQuantity AS stock,price FROM Product WHERE productID=? FOR UPDATE');$lock->execute([$item['id']]);$fresh=$lock->fetch();if(!$fresh||$fresh['stock']<$item['quantity'])throw new RuntimeException($item['name'].' has insufficient stock.');$total+=$fresh['price']*$item['quantity'];}
        $discount=round($total*($discountRates[$discountCode]??0),2);$payable=$total-$discount;$notes=$discountCode!==''?'Discount code: '.$discountCode:null;
        $s=$pdo->prepare("INSERT INTO `Order`(customerID,subtotal,discount,orderStatus,notes) VALUES(?,?,?,'Processing',?)");$s->execute([currentUser()['id'],$total,$discount,$notes]);$orderId=(int)$pdo->lastInsertId();
        foreach($items as $item){$pdo->prepare('INSERT INTO Order_product(orderID,productID,quantity,unitPrice) VALUES(?,?,?,?)')->execute([$orderId,$item['id'],$item['quantity'],$item['price']]);$pdo->prepare('UPDATE Product SET stockQuantity=stockQuantity-? WHERE productID=?')->execute([$item['quantity'],$item['id']]);}
        $pdo->prepare('INSERT INTO Shipment(orderID,receiverName,phone,shippingAddress,shippingFee) VALUES(?,?,?,?,0)')->execute([$orderId,$receiver,$phone,$address]);
        $transaction='GGH-'.date('Ymd').'-'.str_pad((string)$orderId,6,'0',STR_PAD_LEFT);$pdo->prepare("INSERT INTO Payment(orderID,paymentMethod,amount,paymentStatus,transactionID,paid_at) VALUES(?,?,?,'Paid',?,NOW())")->execute([$orderId,$method,$payable,$transaction]);$pdo->commit();unset($_SESSION['cart']);flash('success','Payment successful'.($discount>0?' — '.money($discount).' discount applied':'').'. Order #'.$orderId.' is confirmed.');redirect('user/orders.php');
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash('error',$e instanceof RuntimeException?$e->getMessage():'Order could not be completed.');redirect('user/checkout.php');}
}

if (str_starts_with($action,'admin_')) requireAdmin();
if ($action === 'admin_category_save') {$id=(int)($_POST['id']??0);$name=trim($_POST['name']??'');if($name){$s=$pdo->prepare($id?'UPDATE Category SET categoryName=? WHERE categoryID=?':'INSERT INTO Category(categoryName) VALUES(?)');$s->execute($id?[$name,$id]:[$name]);}flash('success','Category saved.');redirect('admin/index.php?tab=categories');}
if ($action === 'admin_category_delete') {try{$pdo->prepare('DELETE FROM Category WHERE categoryID=?')->execute([(int)$_POST['id']]);flash('success','Category deleted.');}catch(PDOException $e){flash('error','Category has products and cannot be deleted.');}redirect('admin/index.php?tab=categories');}
if ($action === 'admin_product_save') {$id=(int)($_POST['id']??0);$data=[(int)$_POST['category_id'],trim($_POST['name']),trim($_POST['description']),trim($_POST['specifications']),max(0,(float)$_POST['price']),max(0,(int)$_POST['stock']),trim($_POST['image_url'])];if($id){$data[]=$id;$pdo->prepare('UPDATE Product SET categoryID=?,productName=?,description=?,specifications=?,price=?,stockQuantity=?,imageURL=? WHERE productID=?')->execute($data);}else{$pdo->prepare('INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,imageURL) VALUES(?,?,?,?,?,?,?)')->execute($data);}flash('success','Product saved.');redirect('admin/index.php?tab=products');}
if ($action === 'admin_product_delete') {$pdo->prepare("UPDATE Product SET status='Inactive' WHERE productID=?")->execute([(int)$_POST['id']]);flash('success','Product removed from catalog.');redirect('admin/index.php?tab=products');}
if ($action === 'admin_order_status') {$allowed=['Pending','Processing','Delivered','Cancelled'];$status=$_POST['status']??'';if(in_array($status,$allowed,true))$pdo->prepare('UPDATE `Order` SET orderStatus=? WHERE orderID=?')->execute([$status,(int)$_POST['id']]);flash('success','Order status updated.');redirect('admin/index.php?tab=orders');}
if ($action === 'admin_customer_status') {$status=$_POST['status']??'';if(in_array($status,['Active','Inactive'],true))$pdo->prepare("UPDATE Customer SET status=? WHERE customerID=? AND role='customer'")->execute([$status,(int)$_POST['id']]);flash('success','Customer status updated.');redirect('admin/index.php?tab=customers');}
redirect('user/index.php');
