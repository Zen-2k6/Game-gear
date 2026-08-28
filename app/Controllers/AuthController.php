<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\CartService;
use PDO;
use PDOException;

final class AuthController
{
    public function __construct(private readonly PDO $pdo, private readonly CartService $cart)
    {
    }

    public function loginPage(): void
    {
        if (\currentUser()) {
            \redirectTo('home');
        }
        \render('user/login', ['pageTitle' => 'Login', 'cartService' => $this->cart]);
    }

    public function registerPage(): void
    {
        if (\currentUser()) {
            \redirectTo('home');
        }
        \render('user/register', ['pageTitle' => 'Register', 'cartService' => $this->cart]);
    }

    public function profilePage(): void
    {
        \requireLogin();
        $statement = $this->pdo->prepare('SELECT customerName AS name,email,phone,address,created_at FROM Customer WHERE customerID=?');
        $statement->execute([\currentUser()['id']]);
        $profile = $statement->fetch();
        \render('user/profile', compact('profile') + ['pageTitle' => 'My Profile', 'cartService' => $this->cart]);
    }

    public function ordersPage(): void
    {
        \requireLogin();
        $statement = $this->pdo->prepare('SELECT o.orderID AS id,o.orderDate,(o.subtotal-o.discount) AS total_amount,o.discount,o.orderStatus AS status,o.created_at,p.paymentMethod AS payment_method,p.paymentStatus AS payment_status,p.transactionID AS transaction_id FROM `Order` o LEFT JOIN Payment p ON p.orderID=o.orderID WHERE o.customerID=? ORDER BY o.created_at DESC');
        $statement->execute([\currentUser()['id']]);
        $orders = $statement->fetchAll();
        \render('user/orders', compact('orders') + ['pageTitle' => 'My Orders', 'cartService' => $this->cart]);
    }

    public function register(array $input): never
    {
        $name = trim((string) ($input['name'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $phone = trim((string) ($input['phone'] ?? ''));
        $address = trim((string) ($input['address'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || strlen($password) < 8) {
            \flash('error', 'Enter valid details. Password needs at least 8 characters.');
            \redirectTo('register');
        }

        try {
            $statement = $this->pdo->prepare('INSERT INTO Customer(customerName,email,phone,password,address) VALUES(?,?,?,?,?)');
            $statement->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $address]);
            \flash('success', 'Account created. Please log in.');
            \redirectTo('login');
        } catch (PDOException) {
            \flash('error', 'That email address is already registered.');
            \redirectTo('register');
        }
    }

    public function login(array $input): never
    {
        $statement = $this->pdo->prepare("SELECT * FROM Customer WHERE email=? AND status='Active'");
        $statement->execute([strtolower(trim((string) ($input['email'] ?? '')))]);
        $user = $statement->fetch();
        if (!$user || !password_verify((string) ($input['password'] ?? ''), $user['password'])) {
            \flash('error', 'Incorrect email or password.');
            \redirectTo('login');
        }

        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $user['customerID'], 'name' => $user['customerName'], 'email' => $user['email'], 'phone' => $user['phone'], 'address' => $user['address'], 'role' => $user['role']];
        \flash('success', 'Welcome back, ' . $user['customerName'] . '!');
        \redirectTo($user['role'] === 'admin' ? 'admin' : 'home');
    }

    public function logout(): never
    {
        $_SESSION = [];
        session_destroy();
        \redirectTo('home');
    }

    public function updateProfile(array $input): never
    {
        \requireLogin();
        $name = trim((string) ($input['name'] ?? ''));
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $phone = trim((string) ($input['phone'] ?? ''));
        $address = trim((string) ($input['address'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || ($password !== '' && strlen($password) < 8)) {
            \flash('error', 'Enter valid profile details. A new password needs at least 8 characters.');
            \redirectTo('profile');
        }

        try {
            if ($password !== '') {
                $statement = $this->pdo->prepare('UPDATE Customer SET customerName=?,email=?,phone=?,address=?,password=? WHERE customerID=?');
                $statement->execute([$name, $email, $phone, $address, password_hash($password, PASSWORD_DEFAULT), \currentUser()['id']]);
            } else {
                $statement = $this->pdo->prepare('UPDATE Customer SET customerName=?,email=?,phone=?,address=? WHERE customerID=?');
                $statement->execute([$name, $email, $phone, $address, \currentUser()['id']]);
            }
            $_SESSION['user'] = array_merge(\currentUser(), ['name' => $name, 'email' => $email, 'phone' => $phone, 'address' => $address]);
            \flash('success', 'Profile updated.');
        } catch (PDOException) {
            \flash('error', 'That email address is already used by another account.');
        }
        \redirectTo('profile');
    }
}
