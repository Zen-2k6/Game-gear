# GameGear Hub architecture

```text
gamegear/
├── admin/
│   ├── index.php            # Admin dashboard and management pages
│   └── partials/            # Admin-only document header and scripts
├── user/
│   ├── index.php            # Storefront homepage
│   ├── about.php            # About, mission, and store values
│   ├── contact.php          # Contact channels, email form, and FAQs
│   ├── products.php         # Product catalog, search, and filters
│   ├── product.php          # Product details
│   ├── cart.php             # Shopping cart
│   ├── checkout.php         # Checkout
│   ├── orders.php           # Customer orders
│   ├── profile.php          # Customer personal information
│   ├── login.php            # Customer/admin login
│   ├── register.php         # Customer registration
│   └── partials/            # Customer navbar, cart drawer, and footer
├── actions/
│   └── index.php            # POST actions and application mutations
├── config/
│   └── database.php         # Session, app URL, and PDO connection
├── includes/
│   └── functions.php        # Shared PHP helpers
├── assets/
│   ├── css/style.css        # Application styling
│   └── js/app.js            # Frontend behavior
├── database/
│   ├── database.sql         # Full ERD schema and seed catalog
│   ├── add_new_models.sql
│   ├── add_timestamps.sql
│   ├── add_review_uniqueness.sql
│   ├── add_gaming_chairs.sql
│   └── update_product_images.sql
└── index.php                # Storefront entry redirect
```

The user and admin areas have independent layouts. The admin side does not load the customer navbar, cart drawer, announcement bar, or storefront footer. Customer use cases cover account/profile management, catalog browsing, cart operations, checkout/payment/discounts, order history, and product reviews. Admin use cases cover products and stock, category CRUD, order fulfilment, customer access, customer feedback, and sales reports. Root-level PHP files other than `index.php` are compatibility entry points for old URLs.
