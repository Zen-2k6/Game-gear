CREATE DATABASE IF NOT EXISTS gamegear_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gamegear_hub;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS payments, order_products, orders, products, categories, users;
DROP TABLE IF EXISTS Review, Cart_item, Cart, Payment, Shipment, Order_product, `Order`, Product, Category, Customer;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE Customer (
  customerID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customerName VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(30) NOT NULL,
  password VARCHAR(255) NOT NULL,
  address TEXT NULL,
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE Category (
  categoryID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoryName VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE Product (
  productID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoryID INT UNSIGNED NOT NULL,
  productName VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  specifications TEXT NOT NULL,
  price DECIMAL(12,2) UNSIGNED NOT NULL,
  stockQuantity INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  imageURL VARCHAR(1000) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_product_category FOREIGN KEY(categoryID) REFERENCES Category(categoryID)
) ENGINE=InnoDB;

CREATE TABLE Review (
  reviewID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customerID INT UNSIGNED NOT NULL,
  productID INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NULL,
  comment TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_review_customer FOREIGN KEY(customerID) REFERENCES Customer(customerID) ON DELETE CASCADE,
  CONSTRAINT fk_review_product FOREIGN KEY(productID) REFERENCES Product(productID) ON DELETE CASCADE,
  CONSTRAINT uq_review_customer_product UNIQUE(customerID, productID),
  CONSTRAINT chk_review_rating CHECK (rating IS NULL OR rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE Cart (
  cartID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customerID INT UNSIGNED NOT NULL,
  totalItem INT UNSIGNED NOT NULL DEFAULT 0,
  totalAmount DECIMAL(12,2) UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('Active','Converted','Abandoned') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cart_customer FOREIGN KEY(customerID) REFERENCES Customer(customerID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Cart_item (
  cart_item_ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cartID INT UNSIGNED NOT NULL,
  productID INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT uq_cart_product UNIQUE(cartID, productID),
  CONSTRAINT fk_cart_item_cart FOREIGN KEY(cartID) REFERENCES Cart(cartID) ON DELETE CASCADE,
  CONSTRAINT fk_cart_item_product FOREIGN KEY(productID) REFERENCES Product(productID)
) ENGINE=InnoDB;

CREATE TABLE `Order` (
  orderID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  customerID INT UNSIGNED NOT NULL,
  orderDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  subtotal DECIMAL(12,2) UNSIGNED NOT NULL,
  discount DECIMAL(12,2) UNSIGNED NOT NULL DEFAULT 0,
  orderStatus ENUM('Pending','Processing','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  notes TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_customer FOREIGN KEY(customerID) REFERENCES Customer(customerID)
) ENGINE=InnoDB;

CREATE TABLE Order_product (
  order_product_ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  orderID INT UNSIGNED NOT NULL,
  productID INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  unitPrice DECIMAL(12,2) UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_product_order FOREIGN KEY(orderID) REFERENCES `Order`(orderID) ON DELETE CASCADE,
  CONSTRAINT fk_order_product_product FOREIGN KEY(productID) REFERENCES Product(productID)
) ENGINE=InnoDB;

CREATE TABLE Payment (
  paymentID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  orderID INT UNSIGNED NOT NULL UNIQUE,
  paymentMethod VARCHAR(50) NOT NULL,
  amount DECIMAL(12,2) UNSIGNED NOT NULL,
  paymentStatus ENUM('Pending','Paid','Failed') NOT NULL DEFAULT 'Pending',
  transactionID VARCHAR(100) UNIQUE,
  paid_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_payment_order FOREIGN KEY(orderID) REFERENCES `Order`(orderID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Shipment (
  shipmentID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  orderID INT UNSIGNED NOT NULL UNIQUE,
  receiverName VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  shippingAddress TEXT NOT NULL,
  shippingFee DECIMAL(12,2) UNSIGNED NOT NULL DEFAULT 0,
  shipped_at DATETIME NULL,
  trackingNumber VARCHAR(100) NULL UNIQUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_shipment_order FOREIGN KEY(orderID) REFERENCES `Order`(orderID) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO Customer(customerName,email,phone,password,role) VALUES
('GameGear Admin','admin@gamegear.test','09111111111','$2y$12$f9gjjvZFoptS.jeeEtUzrO78FvDEjafWIwPHNnE7PPk87..UpluHq','admin'); //admin password: admin123

INSERT INTO Category(categoryName) VALUES ('Gaming Keyboards'),('Gaming Mice'),('Headsets'),('Controllers'),('RGB Cooling'),('Gaming Chairs');

INSERT INTO Product(categoryID,productName,description,specifications,price,stockQuantity,imageURL) VALUES
(1,'Apex 60 Mechanical Keyboard','Compact mechanical keyboard for fast competitive play.','60% layout\nRed mechanical switches\nRGB backlight\nUSB-C connection',125000,12,'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?auto=format&fit=crop&w=900&q=80'),
(2,'Phantom Pro Gaming Mouse','Lightweight precision mouse with programmable buttons.','12000 DPI sensor\n6 programmable buttons\n68g weight\nBraided cable',78000,18,'https://images.unsplash.com/photo-1527814050087-3793815479db?auto=format&fit=crop&w=900&q=80'),
(3,'Nova 7.1 Gaming Headset','Immersive sound and clear team communication.','Virtual 7.1 surround\nDetachable microphone\nMemory foam cushions\nUSB connection',145000,9,'https://images.unsplash.com/photo-1599669454699-248893623440?auto=format&fit=crop&w=900&q=80'),
(4,'Striker Wireless Controller','Responsive wireless controller for PC gaming.','Bluetooth 5.0\nDual vibration\n12-hour battery\nUSB-C charging',98000,15,'https://images.unsplash.com/photo-1592840496694-26d035b52b48?auto=format&fit=crop&w=900&q=80'),
(5,'Prism ARGB Fan Kit','Three quiet cooling fans with synchronized lighting.','3 × 120mm fans\nAddressable RGB\nController included\n800–1800 RPM',89000,7,'https://images.unsplash.com/photo-1587202372775-e229f172b9d7?auto=format&fit=crop&w=900&q=80'),
(1,'Titan TKL Keyboard','Tenkeyless keyboard with durable blue switches.','TKL layout\nBlue switches\nMetal top plate\nRainbow backlight',110000,10,'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=900&q=80'),
(2,'Viper Air Wireless Mouse','Ultra-light wireless control made for fast flicks and marathon sessions.','26000 DPI sensor\nTri-mode wireless\n59g shell\n70-hour battery',132000,16,'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=900&q=80'),
(3,'Echo Studio Headset','Balanced spatial audio with a broadcast-clear microphone.','50mm drivers\nFlip-to-mute microphone\nCooling gel cushions\n3.5mm + USB',168000,11,'https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?auto=format&fit=crop&w=900&q=80'),
(4,'Arcade Pro Controller','Hall-effect precision built for competitive console and PC play.','Hall-effect sticks\n2 rear paddles\nAdjustable triggers\n20-hour battery',155000,13,'https://images.unsplash.com/photo-1600080972464-8e5f35f63d08?auto=format&fit=crop&w=900&q=80'),
(5,'Halo RGB Light Bars','Reactive desktop lighting that extends every game beyond the screen.','Music sync\n16.8M colors\nApp control\nUSB-C powered',72000,20,'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=900&q=80'),
(1,'Orbit 75 Hot-Swap Keyboard','A premium compact board with a soft gasket feel and full function row.','75% gasket mount\nHot-swappable sockets\nPBT keycaps\nVolume knob',189000,8,'https://images.unsplash.com/photo-1595225476474-87563907a212?auto=format&fit=crop&w=900&q=80'),
(2,'Glide XXL Desk Mat','A smooth control surface sized to anchor your entire setup.','900 × 400mm\nStitched edges\nWater resistant\nAnti-slip base',42000,25,'https://images.unsplash.com/photo-1615750185825-fc85c6aba18d?auto=format&fit=crop&w=900&q=80'),
(1,'Razer BlackWidow V4 75%','A hot-swappable compact mechanical keyboard with immersive underglow.','75% aluminum layout\nHot-swappable switches\nDoubleshot ABS keycaps\nPer-key RGB',385000,9,'https://assets3.razerzone.com/SRsaiRU8_8_QnhoHF2nq5Smizt8=/1920x1280/https%3A%2F%2Fmedias-p1.phoenix.razer.com%2Fsys-master-phoenix-images-container%2Fhf7%2Fh5a%2F9640099119134%2Fblackwidow-v4-75-black-2-500x500.png'),
(1,'Logitech G PRO X TKL Lightspeed','Tournament-focused wireless keyboard with pro-grade response.','Tenkeyless layout\nLIGHTSPEED wireless\nGX mechanical switches\nRGB lighting',520000,6,'https://cdn.cs.1worldsync.com/20/17/2017dd16-e8f1-447a-a996-f9e12cfbf2f0.jpg'),
(1,'Keychron Q1 Max','Premium wireless custom keyboard with a full-metal gasket-mounted build.','75% CNC aluminum body\n2.4GHz and Bluetooth\nHot-swappable PCB\nDouble-gasket design',650000,5,'https://www.keychron.com/cdn/shop/files/Keychron-Q1-Max-QMK-VIA-Wireless-Custom-Mechanical-Keyboard-75_-Layout-Aluminum-White-Fully-Assembled-Knob-for-Mac-Windows-Linux-Gateron-Jupiter-Red.jpg?v=1753685590&width=1200'),
(2,'Logitech G PRO X Superlight 2','Ultra-light wireless esports mouse with a next-generation optical sensor.','HERO 2 sensor\n32000 DPI\n60g weight\nUSB-C charging',445000,12,'https://resource.logitechg.com/w_1200%2Ch_900%2Car_4%3A3%2Cc_pad%2Cq_auto%2Cf_auto/d_transparent.gif/content/dam/gaming/en/products/pro-x-superlight-2/new-gallery-assets-2025/pro-x-superlight-2-mice-top-angle-white-gallery-1.png'),
(2,'Razer Viper V3 Pro','Symmetrical wireless mouse tuned with professional esports players.','Focus Pro 35K sensor\n54g weight\n8000Hz polling\n95-hour battery',470000,10,'https://asset.productmarketingcloud.com/api/assetstorage/1995_a50426df-6945-4d1c-a23b-5ab1ddb31e5b/3088392.jpg'),
(3,'SteelSeries Arctis Nova 7 Wireless','Multi-platform wireless headset for gaming, calls, and music.','2.4GHz plus Bluetooth\n360° spatial audio\n38-hour battery\nRetractable microphone',495000,8,'https://images.ctfassets.net/hmm5mo4qf4mf/3p2rnBYIX4crdjTafcDJGQ/07b9db56b7acca8513a3250d5e52c1ab/arctis_nova_pdp_img_buy_1.png__1920x1080_crop-fit_optimize_subsampling-2-177.png?fit=scale&fm=webp&q=90&w=1200'),
(3,'HyperX Cloud III Wireless','Long-lasting wireless comfort with clear positional game audio.','120-hour battery\n53mm angled drivers\nDetachable microphone\nMemory foam cushions',430000,11,'https://hp.widen.net/content/ila3sgmjzt/png/ila3sgmjzt.png?color=ffffff00&dpi=72&h=900&w=1200'),
(4,'Xbox Wireless Controller 2025','Refined wireless controller for Xbox, PC, mobile, and cloud gaming.','Bluetooth and Xbox Wireless\nHybrid D-pad\nTextured triggers\nShare button',285000,14,'https://cms-assets.xboxservices.com/assets/62/96/62965c6a-429c-47b1-b901-ff69a6081d43.jpg?n=Xbox-Wireless-Controller_Image-Hero-768_Black_1920x831_03.jpg'),
(4,'DualSense Edge Wireless Controller','Customizable high-performance controller with replaceable stick modules.','Adjustable trigger stops\nRemappable back buttons\nChangeable stick caps\nUSB-C connection',720000,4,'https://gmedia.playstation.com/is/image/SIEPDC/DualSense-Edge-image-block-05-en-03oct22?$1600px$'),
(5,'Corsair iCUE LINK QX120 RGB Kit','Smart daisy-chain cooling fans with vivid dual-zone lighting.','3 × 120mm PWM fans\n34 RGB LEDs per fan\nMagnetic dome bearing\niCUE LINK hub included',390000,7,'https://www.scan.co.uk/images/infopages/corsair_fans/QX120/starterkit/topimgb.png'),
(5,'Lian Li UNI FAN TL LCD 120 Kit','Premium modular fans with customizable edge displays.','3 × 120mm fans\n1.6-inch LCD displays\nDaisy-chain design\nL-Connect 3 control',510000,6,'https://www.proshop.de/Images/1600x1200/3224052_f22e6dd29ceb.jpg'),
(6,'Vortex Elite Gaming Chair','Ergonomic gaming chair designed for long competitive sessions.','High-back ergonomic design\nAdjustable lumbar support\n4D armrests\nReclines up to 155 degrees',460000,8,'https://www.topmarket.co.il/images/detailed/58/1Chair_RED_03.jpg'),
(6,'Noble Ice Gaming Chair','Premium white and black racing chair with full ergonomic adjustment.','Cold-foam seat\n4D armrests\nAdjustable neck and lumbar cushions\nClass-4 gas lift',590000,6,'https://www.dateks.lv/images/pic/1200/1200/867/2212.jpg'),
(6,'Crimson Racer Gaming Chair','Sport-inspired gaming chair with supportive cushions and a sturdy base.','High-back racing design\nRemovable lumbar cushion\nHeight and tilt adjustment\nFive-wheel metal base',385000,10,'https://img.advice.co.th/images_nas/pic_product4/A0103856/A0103856OK_BIG_2.jpg'),
(6,'Quelman Pro Gaming Chair','Wide ergonomic gaming chair made for comfortable extended sessions.','Molded foam padding\nAdjustable armrests\nReclining backrest\nSupports up to 136 kg',525000,7,'https://target.scene7.com/is/image/Target/GUEST_e59a5ead-57a4-4c49-8d74-e23d1ba037d6');
